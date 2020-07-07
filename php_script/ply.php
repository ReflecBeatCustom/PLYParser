<?php

function getint1(&$fh)
{
	$tmp = fread($fh, 1);
	return unpack("c",$tmp)[1];
}

function getint2(&$fh)
{
	$tmp = fread($fh, 2);
	return unpack("s",$tmp)[1];
}

function getint4(&$fh)
{
	$tmp = fread($fh, 4);
	return unpack("l",$tmp)[1];
}

function getnull(&$fh,$count)
{
	$tmp = fread($fh, $count);
}

function getfloat(&$fh)
{
	$tmp = fread($fh, 4);
	return unpack("f",$tmp)[1];
}

const HELP = "PLY to JSON Converter.\n" .
  "By Ayulsa\n" .
  "v1.0\n" .
  "Usage:\n" .
  "php ply.php input\n" .
  "key options:\n";


if ($argc <1) {
  echo("Wrong number of arguments!\n");
  echo(HELP);
  return;
}

$filepath = $argv[1];
$fh = fopen($filepath,"rb");

$chart -> header = getint4($fh);
$chart -> type = getint1($fh);

getnull($fh,11);

$chart -> startbpm = getfloat($fh);
$chart -> length = getint4($fh);

getnull($fh,4);

$chart -> numnotes = getint2($fh);
$chart -> numbpmchanges = getint2($fh);
$chart -> numgeneratednotes = getint2($fh);

getnull($fh,2);

$chart -> numsopoints = getint2($fh);

getnull($fh,6);

$chart -> notes = array();

for ($i = 1; $i <= $chart -> numnotes; $i++)
{
	$note -> starttime = getint4($fh);
	$note -> flytime = getint4($fh);
	$note -> id = getint2($fh);
	$note -> source = getint2($fh);
	$note -> numreflectnotes = getint2($fh);
	$note -> reflectnotes = array();
	for ($j = 1; $j <= $note -> numreflectnotes; $j++)
		$note -> reflectnotes[] = getint2($fh);
	$note -> numalsoreflectednotes = getint1($fh);
	$note -> side = getint1($fh);
	$note -> istop = getint1($fh);
	$note -> type = getint1($fh);
	$note -> lolength = getint2($fh);
	$note -> position = getint2($fh);
	$note -> isset = getint1($fh);
	getnull($fh,3);
	$note -> magicnumber = getint1($fh);
	getnull($fh,11);

	if ($note -> magicnumber & 8)
	{
		$note -> chainlastid = getint2($fh);
		$note -> chainnextid = getint2($fh);
		$note -> chainid = getint2($fh);
		$note -> chaindeltatime = getint2($fh);
		getnull($fh,4);
	}
	else
	{
		$note -> chainlastid = 0;
		$note -> chainnextid = 0;
		$note -> chainid = 0;
		$note -> chaindeltatime = 0;
	}

	$chart ->notes[] = clone $note;
}

$chart -> bpmchanges = array();

for ($i = 1; $i <= $chart -> numbpmchanges; $i++)
{
	getnull($fh,2);
	$event -> id = getint2($fh);
	$event -> time = getint4($fh);
	getnull($fh,8);
	$event -> value = getfloat($fh);
	getnull($fh,16);

	$chart ->bpmchanges[] = clone $event;
}

$chart -> sopoints = array();

for ($i = 1; $i <= $chart -> numsopoints; $i++)
{
	$sopoint -> noteid = getint2($fh);
	$sopoint -> id = getint2($fh);
	$sopoint -> position = getint2($fh);
	getnull($fh,2);
	$sopoint -> starttime = getint4($fh);
	$sopoint -> flytime = getint4($fh);

	$chart ->sopoints[] = clone $sopoint;
}

fclose($fh);
file_put_contents("output.json", json_encode($chart));
?>
