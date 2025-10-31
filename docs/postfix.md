Postfix Intallation & Configuration 
===================================

### Enable the necessary repositories
* RHEL Compatibility OS (CentOS, Alma Linux, Rocky Linux, ...)
First, you’ll need to enable the EPEL repository by running:
```sh
dnf -y install epel-release
```

Then you’ll have to enable the CodeReady builder repository:
```sh
dnf config-manager –set-enabled crb
```

### Install Postfix and the related tools
```sh
dnf -y install postfix postfix-ldap postfix-lmdb postfix-mysql postfix-pcre
```

### Configure Postfix
Postfix configuration files are located in /etc/postfix,
so before making any changes make a backup:
```sh
cp /etc/postfix/main.cf{,.orig}
cp /etc/postfix/master.cf{,.orig}
```

#### Configure access control by using policy service
Type the following commands to edit the Postfix main configuration file:
```sh
postconf -e "smtpd_recipient_restrictions=check_policy_service inet:127.0.0.1:1403,reject"
postconf -e "smtpd_end_of_data_restrictions=check_policy_service inet:127.0.0.1:1403,reject"
postconf -e smtpd_relay_restrictions=permit
```
Note: Replace `127.0.0.1:1403` with your policy service host and port.

#### Configure access control by using lmdb database file
Type the following commands to create lmdb database files:
```sh
touch /etc/postfix/client_ip_access
touch /etc/postfix/sender_access
touch /etc/postfix/recipient_restriction
postmap lmdb:/etc/postfix/client_ip_access
postmap lmdb:/etc/postfix/sender_access
postmap lmdb:/etc/postfix/recipient_restriction
```

Type the following commands to configure client accesses:
```sh
postconf -e smtpd_client_restrictions=check_client_access lmdb:/etc/postfix/client_ip_access,reject
postconf -e smtpd_sender_restrictions=check_sender_access lmdb:/etc/postfix/sender_access,reject
postconf -e smtpd_relay_restrictions=check_client_access lmdb:/etc/postfix/client_ip_access,reject
postconf -e smtpd_recipient_restrictions=check_recipient_access lmdb:/etc/postfix/recipient_restriction
```

#### Configure sender tranport
Type the following commands to configure tranport maps for sender:
```sh
touch /etc/postfix/sender_transport
postconf -e sender_dependent_default_transport_maps=lmdb:/etc/postfix/sender_transport
postmap lmdb:/etc/postfix/sender_transport
```

#### Configure out with rate control

* Type the following command to edit the Postfix master configuration file:
```sh
vi /etc/postfix/master.cf
```

* Add out transport with rabbit rate control:
```
rabbit    unix  -       -       n       -       -       smtp
   -o syslog_name=postfix/rabbit
   -o rabbit_destination_concurrency_limit=20
   -o rabbit_destination_rate_delay=0s
   -o rabbit_destination_recipient_limit=50
   -o rabbit_initial_destination_concurrency=5
```

* Add out transport with turtle rate control:
```
turtle    unix  -       -       n       -       -       smtp
   -o syslog_name=postfix/turtle
   -o turtle_destination_concurrency_limit=1
   -o turtle_destination_rate_delay=1s
   -o turtle_destination_recipient_limit=2
   -o turtle_initial_destination_concurrency=1
```

#### Restart Postfix service
```sh
systemctl restart postfix
```

#### Test email sending:
```sh
smtp-source -f sender@example.com -t recipient@example.com -S "Test send mail" 127.0.0.1:25
```
