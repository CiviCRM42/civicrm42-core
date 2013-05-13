#!/usr/bin/env python
'''
Push changes in subversion repository to IRC channel

CREDITS
Miki Tebeka, http://pythonwise.blogspot.com/2009/05/subversion-irc-bot.html
adapted for the CiviCRM project by Adam Wight
'''

from twisted.words.protocols import irc
from twisted.internet.protocol import ReconnectingClientFactory
import re
from subprocess import Popen, PIPE
from xml.etree.cElementTree import parse as xmlparse
from cStringIO import StringIO

root = "http://svn.civicrm.org/civicrm"

class IRCClient(irc.IRCClient):
    nickname = "civi-svn"
    realname = "Subversion Bot"
    channel = "#civicrm"
    maxlen = 120

    instance = None # Running instance

    def signedOn(self):
        IRCClient.instance = self
        self.join(self.channel)

    def svn(self, revision, author, comment):
        comment = (comment[:(maxlen-3)] + "...") if  len(comment) > maxlen else comment
        message = "r%s by %s: %s" % (revision, author, comment)
        self.say(self.channel, message)


class SVNPoller:
    def __init__(self, root, auth_args):
        self.pre = ["svn", "--xml"] + auth_args
        self.root = root
        self.last_revision = self.get_last_revision()

    def check(self):
        if not IRCClient.instance:
            return

        try:
            last_revision = self.get_last_revision()
            if (not last_revision) or (last_revision == self.last_revision):
                return

            for rev in range(self.last_revision + 1, last_revision + 1):
                author, comment = self.revision_info(rev)
                IRCClient.instance.svn(rev, author, comment)
            self.last_revision = last_revision
        except Exception, e:
            print "ERROR: %s" % e

    def svn(self, *cmd):
        pipe = Popen(self.pre +  list(cmd) + [self.root], stdout=PIPE)
        try:
            data = pipe.communicate()[0]
        except IOError:
            data = ""
        return xmlparse(StringIO(data))

    def get_last_revision(self):
        tree = self.svn("info")
        revision = tree.find("//commit").get("revision")
        return int(revision)

    def revision_info(self, revision):
        tree = self.svn("log", "-r", str(revision))
        author = tree.find("//author").text
        comment = tree.find("//msg").text

        return author, comment

if __name__ == "__main__":
    from twisted.internet import reactor
    from twisted.internet.task import LoopingCall

    if len(sys.argv) < 4:
        print "Usage: svnbot --username SVN_USER --password SVN_PASS"
        sys.exit(1)

    factory = ReconnectingClientFactory()
    factory.protocol = IRCClient
    reactor.connectTCP("irc.freenode.net", 6667, factory)

    poller = SVNPoller(root, sys.argv[1:])
    task = LoopingCall(poller.check)
    task.start(60)

    reactor.run()
