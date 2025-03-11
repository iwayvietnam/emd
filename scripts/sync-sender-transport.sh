#!/usr/bin/env bash

## Set variables
SENDER_TRANSPORT=/etc/postfix/sender_transport
MYSQL_QUERY="SELECT sender, transport FROM sender_transports"

# Clean up when done or when aborting.
trap 'rm -f sender_transport.$$' 0 1 2 3 15

usage ()
{
    echo -e "Usage:"
    echo -e "$0 [OPTIONS], where options can be:"
    echo -e "\t-s, --mysql-server=<MYSQL_SERVER>     set the mysql server. Ex: --mysql-server=localhost"
    echo -e "\t-u, --mysql-user=<MYSQL_USER>         set user to login. Ex: --mysql-user=foo"
    echo -e "\t-p, --mysql-password=<MYSQL_PASSWORD> set password to login. Ex: --mysql-password=s3cr3t"
    echo -e "\t-d, --mysql-database=<MYSQL_DATABASE> set mysql database. Ex: --mysql-database=bar"
}

for arg in "$@"
do
    case $arg in
        -s=*|--mysql-server=* )
            MYSQL_SERVER="${arg#*=}";;
        -u=*|--mysql-user=* )
            MYSQL_USER="${arg#*=}";;
        -p=*|--mysql-password=* )
            MYSQL_PASSWORD="${arg#*=}";;
        -a=*|--mysql-database=* )
            MYSQL_DATABASE="${arg#*=}";;
        -h|--help )
            usage
            exit 1;;
        * )
            usage
            exit 1;;
    esac
done

if [[ -z "$@" ]]; then
    usage
    exit 1
fi

mysql -u $MYSQL_USER --password=$MYSQL_PASSWORD -h $MYSQL_SERVER -D $MYSQL_DATABASE -e $MYSQL_QUERY | tr '\t' ' ' > sender_transport.$$
sed -i 1d sender_transport.$$
cp -f sender_transport.$$ $SENDER_TRANSPORT
postmap lmdb:$SENDER_TRANSPORT
