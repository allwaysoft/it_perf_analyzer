cd /opt/lampp/htdocs/bi_server/metabase
sh kill_metabase.sh
rm -f nohup.out
nohup sudo java -Dh2.bindAddress=localhost -Xmx1024m -jar metabase.jar &
