<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class LibrarySoapController extends Controller
{
    /**
     * Menyajikan dokumen kontrak WSDL 1.1 ringkas (GET /soap/sipus.wsdl)
     */
    public function wsdl(): Response
    {
        $endpointUrl = url('/soap/sipus');

        $wsdlXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="LibrarySoapService"
    targetNamespace="http://sipus.del.ac.id/soap/clearance"
    xmlns:tns="http://sipus.del.ac.id/soap/clearance"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns="http://schemas.xmlsoap.org/wsdl/">

    <!-- 1. Message: Spesifikasi parameter input dan output -->
    <message name="CheckClearanceStatusRequest">
        <part name="nim_peminjam" type="xsd:string"/>
    </message>

    <message name="CheckClearanceStatusResponse">
        <part name="nim_peminjam" type="xsd:string"/>
        <part name="active_loans" type="xsd:int"/>
        <part name="is_cleared" type="xsd:boolean"/>
    </message>

    <!-- 2. PortType: Daftar operasi yang disediakan -->
    <portType name="LibraryPortType">
        <operation name="CheckClearanceStatus">
            <input message="tns:CheckClearanceStatusRequest"/>
            <output message="tns:CheckClearanceStatusResponse"/>
        </operation>
    </portType>

    <!-- 3. Binding: Mengikat operasi ke protokol SOAP over HTTP RPC -->
    <binding name="LibrarySoapBinding" type="tns:LibraryPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="CheckClearanceStatus">
            <soap:operation soapAction="http://sipus.del.ac.id/soap/clearance#CheckClearanceStatus"/>
            <input>
                <soap:body use="literal" namespace="http://sipus.del.ac.id/soap/clearance"/>
            </input>
            <output>
                <soap:body use="literal" namespace="http://sipus.del.ac.id/soap/clearance"/>
            </output>
        </operation>
    </binding>

    <!-- 4. Service: Alamat URL fisik endpoint SOAP -->
    <service name="LibrarySoapService">
        <port name="LibraryPort" binding="tns:LibrarySoapBinding">
            <soap:address location="{$endpointUrl}"/>
        </port>
    </service>
</definitions>
XML;

        return response($wsdlXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * Memproses request SOAP Envelope (POST /soap/sipus)
     */
    public function handle(Request $request): Response
    {
        try {
            $rawXml = trim($request->getContent());

            if ($rawXml === '') {
                return $this->soapFault('soap:Client', 'Body XML SOAP tidak boleh kosong.');
            }

            // Parsing XML dengan aman tanpa entity expansion eksternal
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($rawXml);

            if ($xml === false) {
                return $this->soapFault('soap:Client', 'Format XML tidak valid (Malformed XML).');
            }

            // Ambil elemen nim_peminjam secara fleksibel terhadap namespace
            $nimNodes = $xml->xpath('//*[local-name()="nim_peminjam"]');

            if (empty($nimNodes)) {
                return $this->soapFault(
                    'soap:Client',
                    'Operasi tidak dikenali atau elemen <nim_peminjam> tidak ditemukan pada CheckClearanceStatusRequest.'
                );
            }

            $nim = trim((string) $nimNodes[0]);

            if ($nim === '') {
                return $this->soapFault('soap:Client', 'Nilai elemen <nim_peminjam> tidak boleh kosong.');
            }

            // Keputusan Desain #1: Cek apakah mahasiswa terdaftar di tabel users
            $user = User::where('nim', $nim)->first();

            if (!$user) {
                return $this->soapFault(
                    'soap:Client',
                    "NIM '{$nim}' tidak terdaftar di sistem perpustakaan."
                );
            }

            // Hitung pinjaman aktif nyata (status 'dipinjam') milik user ber-NIM tersebut
            $activeLoans = Peminjaman::where('user_id', $user->id)
                ->where('status', Peminjaman::STATUS_DIPINJAM)
                ->count();

            $isCleared = $activeLoans === 0 ? 'true' : 'false';

            $safeNim = htmlspecialchars($nim, ENT_XML1, 'UTF-8');

            $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Header>
        <m:ProviderInfo>SIPUS-Del Legacy SOAP Engine v1.0</m:ProviderInfo>
    </soap:Header>
    <soap:Body>
        <m:CheckClearanceStatusResponse>
            <m:nim_peminjam>{$safeNim}</m:nim_peminjam>
            <m:active_loans>{$activeLoans}</m:active_loans>
            <m:is_cleared>{$isCleared}</m:is_cleared>
        </m:CheckClearanceStatusResponse>
    </soap:Body>
</soap:Envelope>
XML;

            return response($responseXml, 200)
                ->header('Content-Type', 'text/xml; charset=utf-8');
        } catch (Throwable $e) {
            return $this->soapFault('soap:Server', 'Terjadi kesalahan internal pada server SOAP.');
        }
    }

    /**
     * Membentuk respons error standar protokol SOAP (<soap:Fault>)
     */
    private function soapFault(string $faultCode, string $faultMessage): Response
    {
        $safeMsg = htmlspecialchars($faultMessage, ENT_XML1, 'UTF-8');

        $faultXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>{$faultCode}</faultcode>
            <faultstring>{$safeMsg}</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
XML;

        return response($faultXml, 500)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
