echo "Demarrage BI Server ..."
cd /opt/lampp/htdocs/bi_server/metabase
rm -f nohup.out

export MB_PLUGINS_DIR=/opt/lampp/htdocs/bi_server/metabase/plugins

export ram=`free -m | grep "^Mem"|awk '{print $2}'`
export xmx=$(( $ram/2 ))

nohup java -Xmx${xmx}m -jar metabase.jar &
