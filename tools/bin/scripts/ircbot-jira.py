#!/usr/bin/env python
'''
Poll JIRA atom feed and push to IRC channel

TODO decouple irc from atom reader

CREDITS
includes code taken from
Miki Tebeka, http://pythonwise.blogspot.de/2009/05/subversion-irc-bot.html
Eloff, http://stackoverflow.com/a/925630
adapted for the CiviCRM project by Adam Wight
'''

from twisted.words.protocols import irc
from twisted.internet.protocol import ReconnectingClientFactory
from HTMLParser import HTMLParser
import feedparser
import re

source = "http://issues.civicrm.org/jira/activity?maxResults=20&streams=key+IS+CRM&title=undefined"

class IRCClient(irc.IRCClient):
    nickname = "civi-jira"
    realname = "JIRA Bot"
    channel = "#civicrm"
    maxlen = 120

    instance = None # Running instance

    def signedOn(self):
        IRCClient.instance = self
        self.join(self.channel)

    def push(self, author, issue, summary):
        summary = (summary[:(self.maxlen-3)] + "...") if  len(summary) > self.maxlen else summary
        message = "%s: %s %s" % (author, issue, summary)
        self.say(self.channel, str(message))


class FeedPoller:
    last_seen_id = None

    def __init__(self, source):
        self.source = source

    def check(self):
        if not IRCClient.instance:
            return

        result = feedparser.parse(self.source)
        for entry in result.entries:
            if (not self.last_seen_id) or (self.last_seen_id == entry.id):
                break
            m = re.search(r'(CRM-[0-9]+)$', entry.link)
            if (not m) or (entry.generator_detail.href != "http://issues.civicrm.org/jira"):
                continue
            issue = m.group(1)
            IRCClient.instance.push(entry.author_detail.name, issue, strip(entry.summary))
        if result.entries:
            self.last_seen_id = result.entries[0].id


class MLStripper(HTMLParser):
    def __init__(self):
        self.reset()
        self.fed = []
    def handle_data(self, d):
        self.fed.append(d)
    def get_data(self):
        return ''.join(self.fed)

def strip(html):
    stripper = MLStripper()
    stripper.feed(html)
    s = stripper.get_data()
    return s.strip().replace("\n", " ")


if __name__ == "__main__":
    from twisted.internet import reactor
    from twisted.internet.task import LoopingCall

    factory = ReconnectingClientFactory()
    factory.protocol = IRCClient
    reactor.connectTCP("irc.freenode.net", 6667, factory)

    poller = FeedPoller(source)
    task = LoopingCall(poller.check)
    task.start(10)

    reactor.run()
