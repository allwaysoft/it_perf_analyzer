ps -ef|grep metabase|grep -v grep|awk '{print $2}'|xargs kill -9
exit $?
