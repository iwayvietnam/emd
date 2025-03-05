#!/bin/bash
set -e

export PATH=/usr/local/bin:/usr/local/sbin:/bin:/sbin:/usr/bin:/usr/sbin:/usr/X11R6/bin

DKIM_ENABLE=${DKIM_ENABLE:-false}
DKIM_SERVICE=${DKIM_SERVICE:-inet:127.0.0.1:8891}
POLICY_ENABLE=${POLICY_ENABLE:-false}
POLICY_SERVICE=${POLICY_SERVICE:-inet:127.0.0.1:1403}

# DKIM configuration
if [[ "$DKIM_ENABLE" == "true" ]]; then
    postconf -e milter_protocol=2
    postconf -e milter_default_action=accept
    postconf -e smtpd_milters=DKIM_SERVICE
    postconf -e non_smtpd_milters=DKIM_SERVICE
fi

# POLICY configuration
if [[ "$POLICY_ENABLE" == "true" ]]; then
    postconf -e "smtpd_recipient_restrictions=permit_mynetworks, check_policy_service $POLICY_SERVICE, reject"
    postconf -e "smtpd_end_of_data_restrictions=permit_mynetworks, check_policy_service $POLICY_SERVICE, reject"
fi

/usr/sbin/postfix -c /etc/postfix start >/dev/null 2>&1
/usr/sbin/postfix -c /etc/postfix abort >/dev/null 2>&1
/usr/libexec/postfix/master -c /etc/postfix -d 2>&1

rm -f /var/run/rsyslogd.pid
/usr/sbin/rsyslogd -n 2>&1
