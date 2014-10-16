dirname=avancore
find ./ -name ".*" > .dot-files
find ./ -name ".*" -type d >> .dot-files
find ./ -name "*~" >> .dot-files
echo -e "\n.dot-files" >> .dot-files
mkdir "../$dirname-$1"
tar -cf "../$dirname-$1.tar" --newer-mtime="$1 $2" --exclude-from=.dot-files --exclude=cache  --exclude=configuration.php --exclude=tmp --exclude=error_log --exclude=.svn --dereference --hard-dereference ./
tar -xf "../$dirname-$1.tar" -C "../$dirname-$1"
rm "../$dirname-$1.tar"
pushd "../$dirname-$1"
#perl -MFile::Find -e"finddepth(sub{rmdir},'.')"
popd
rm .dot-files
