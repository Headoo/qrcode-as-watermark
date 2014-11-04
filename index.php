<?php
ini_set('memory_limit', '1024M');

require 'vendor/autoload.php';
require 'src/WatImage.php';
require 'src/Qaw.php';


$picturesFolder             = (string) (filter_input(INPUT_GET, 'picturesFolder')) ? filter_input(INPUT_GET, 'picturesFolder') : 'Headoo';
$doneFolder                 = (string) (filter_input(INPUT_GET, 'doneFolder'))     ? filter_input(INPUT_GET, 'doneFolder')     : 'Dropbox/Headoo';
$treatedFolder              = (string) (filter_input(INPUT_GET, 'treatedFolder'))  ? filter_input(INPUT_GET, 'treatedFolder')  : 'Headoo/print';
$recoveryFolder             = (string) (filter_input(INPUT_GET, 'recoveryFolder')) ? filter_input(INPUT_GET, 'recoveryFolder') : 'Headoo/saved';
$hardDrive                  = (string) (filter_input(INPUT_GET, 'hardDrive'))      ? filter_input(INPUT_GET, 'hardDrive')      : 'C';
$url                        = (string) (filter_input(INPUT_GET, 'url'))            ? filter_input(INPUT_GET, 'url')            : 'https://headoo.com/qr/';


$qaw                        = new src\Qaw();
$qaw->rightPath             = __DIR__ . '/../../../../';
$qaw->rightHardDrive        = $hardDrive;
$qaw->qrCodeWidthRatio      = 33;
$qaw->getHardDrive();
$qaw->outputFileExtension   = '.jpg';
$qaw->setPicturesFolder($picturesFolder);
$qaw->setDoneFolder($doneFolder);
$qaw->setTreatedFolder($treatedFolder);
$qaw->createNewFolder($doneFolder . '/DEALT');
$qaw->setRecoveryFolder($recoveryFolder);
$qaw->listDirectoryPicturesAndApplyId();
$qaw->createQrCode($url, $padding = 30);
$qaw->createIniFile();
$qaw->saveCurrentFilesTo('recovery');
$qaw->applyQrCodeAsWatermark(100, 'bottom right');
$qaw->saveCurrentFilesTo('treated');
$qaw->moveTo('recovery', 'done');
$qaw->delete();
