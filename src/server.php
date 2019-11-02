<?php
/**
 *  WebSocket server for RawBT
 *
 *  Based on PHP POS Print (Local Server)
 *  https://github.com/Tecdiary/ppp
 *  MIT License
 *
 *  Modified by 402d (oleg@muraveyko.ru)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Noodlehaus\Config;

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\UriPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

try {

    echo '> Starting server on ws://127.0.0.1:40213 ...', "\n";

    $websocket = new Hoa\Websocket\Server(
        new Hoa\Socket\Server('ws://127.0.0.1:40213')
    );

    $websocket->on('open', function (Hoa\Event\Bucket $bucket) {
        echo '> Connected', "\n";
        return;
    });

    $websocket->on('message', function (Hoa\Event\Bucket $bucket) {
        $data = $bucket->getData();
        echo '> Received request ', "\n";

        $toprint = $data['message'];
        $toprint = str_replace("intent:base64,", "", $toprint);
        $toprint = str_replace("#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;", "", $toprint);
        $toprint = base64_decode($toprint);


        $conf = Config::load('server.json');

        if ($conf->get('PrintConnector.Type') == 'Network') {
            set_time_limit($conf->get('PrintConnector.Params.timeout', 10) + 10);
            $connector = new NetworkPrintConnector($conf->get('PrintConnector.Params.ip', '127.0.0.1'), $conf->get('PrintConnector.Params.port', 9100), $conf->get('PrintConnector.Params.timeout', 10));
        } elseif ($conf->get('PrintConnector.Type') == 'Uri') {
            $connector = UriPrintConnector::get($conf->get('PrintConnector.Params.uri', 'tcp://127.0.0.1:9100'));
        } elseif ($conf->get('PrintConnector.Type') == 'Cups') {
            $connector = new CupsPrintConnector($conf->get('PrintConnector.Params.dest'));
        } elseif ($conf->get('PrintConnector.Type') == 'File') {
            $connector = new FilePrintConnector($conf->get('PrintConnector.Params.filename'));
        } else { // 'Windows'
            $connector = new WindowsPrintConnector($conf->get('PrintConnector.Params.dest', 'LPT1'));
        }
        $connector->write($toprint);
        $connector->finalize();
        echo '> Done print task ', "\n";
        return;
    });

    $websocket->on('close', function (Hoa\Event\Bucket $bucket) {
        echo '> Disconnected', "\n";
        return;
    });

    try {
        echo '> Server started', "\n";
        $websocket->run();
    } catch (Exception $e) {
        echo '> Error occurred, server stopped. ', $e->getMessage(), "\n";
    }
} catch (Exception $e) {
    echo '> Error: ', $e->getMessage(), "\n";
}

