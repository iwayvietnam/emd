OpenDKIM Installation & Configuration
=====================================
### Introduction

Setting up OpenDKIM is essential for creating a trusted email server that ensures your emails won’t be marked as spam. In this tutorial, we’ll walk you through the steps of installing and configuring OpenDKIM

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

### Install OpenDKIM and the related tools
```sh
dnf -y install opendkim opendkim-tools opendbx-mysql
```

### Configure OpenDKIM
OpenDKIM main configuration file is /etc/opendkim.conf, so before making any changes make a backup:
```sh
cp /etc/opendkim.conf{,.orig}
```

Type the following command to edit the OpenDKIM configuration file:
```sh
vi /etc/opendkim.conf
```

Add/edit the following information:
```
LogWhy                  Yes
Syslog                  Yes
SyslogSuccess           Yes
Mode                    sv
Canonicalization        relaxed/simple
SignatureAlgorithm      rsa-sha256
Socket                  inet:8891@localhost
PidFile                 /var/run/opendkim/opendkim.pid
UMask                   022
UserID                  opendkim:opendkim
```

Config `SigningTable` & `KeyTable` with mysql:
```
SigningTable dsn:mysql://user:password@port+host/database/table=dkim_keys?keycol=domain?datacol=id
KeyTable     dsn:mysql://user:password@port+host/database/table=dkim_keys?keycol=id?datacol=domain,selector,private_key
```

Config `SigningTable` & `KeyTable` with refile:
```
SigningTable refile:/etc/opendkim/SigningTable
KeyTable     refile:/etc/opendkim/KeyTable
```


### Enable and start OpenDKIM service
```sh
systemctl enable opendkim
systemctl start opendkim
```

### Integrate OpenDKIM with Postfix
To integrate OpenDKIM with Postfix, type the following commands to edit the Postfix configuration file:
```sh
postconf -e milter_protocol=2
postconf -e milter_default_action=accept
postconf -e smtpd_milters=inet:127.0.0.1:8891
postconf -e non_smtpd_milters=inet:127.0.0.1:8891
```
Note: Replace `127.0.0.1:8891` with your OpenDKIM host and port.

Restart Postfix service
```sh
systemctl restart postfix
```
