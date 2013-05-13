#!/bin/sh

curl http://crm_43/sites/crm_43/modules/civicrm/extern/ipn.php?reset=1\&module=contribute\&contactID=102\&contributionID=14\&contributionRecur=1\&contributionPage=1 -d mc_gross=10.00 -d txn_id=3c7c710dd6c11871012d6d5439313b7e -d invoice=3c7c710dd6c11871012d6d5439313b7e -d payment_status=Completed -d payment_fee=1.00