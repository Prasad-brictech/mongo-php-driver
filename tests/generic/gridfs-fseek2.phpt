--TEST--
GridFS: Testing fseek and fread (2)
--SKIPIF--
<?php require_once "tests/utils/standalone.inc"; ?>
--FILE--
<?php
require_once "tests/utils/server.inc";
function readRange($fp, $seek, $length = false)
{
	fseek($fp, $seek, SEEK_SET);
	$data = '';
	if ($length === false) {
		while (!feof($fp)) {
			$buffer = fread($fp, 8192);
			$data .= $buffer;
		}
	} else {
		$toRead = $length - $seek;
		while ($toRead > 0) {
			$buffer = fread($fp, $toRead);
			$toRead -= strlen($buffer);
			$data .= $buffer;
		}
	}
	return $data;
}

$m = new_mongo_standalone();
$db = $m->selectDb('phpunit');
$grid = $db->getGridFS('wrapper');
$grid->drop();
$grid->storeFile('tests/data-files/gridfs-fseek2-data.txt');

$file = $grid->findOne(array('filename' => 'tests/data-files/gridfs-fseek2-data.txt'));
echo $file->file['chunkSize'], "\n";
$fp = $file->getResource();

echo md5(readRange($fp, 0, 819300)), "\n";
echo md5(readRange($fp, 0, 819300)), "\n";
$first = readRange($fp, 0, 819300);
echo md5(readRange($fp, 819300, false)), "\n";
echo md5(readRange($fp, 819300, false)), "\n";
echo md5(readRange($fp, 819300, false)), "\n";
echo md5(readRange($fp, 819300, false)), "\n";
$second = readRange($fp, 819300, false);
echo md5_file('tests/data-files/gridfs-fseek2-data.txt'), "\n";
echo md5($first . $second), "\n";
?>
--EXPECT--
261120
3c1fbf79189651e9e0d81058cd3c6af6
3c1fbf79189651e9e0d81058cd3c6af6
856bebb64ad591da27e61a9d288a0dce
856bebb64ad591da27e61a9d288a0dce
856bebb64ad591da27e61a9d288a0dce
856bebb64ad591da27e61a9d288a0dce
1769daba434b221ac2577e4f6f491cca
1769daba434b221ac2577e4f6f491cca
