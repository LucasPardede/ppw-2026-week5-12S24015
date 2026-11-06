<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoapClearanceTest extends TestCase
{
    use RefreshDatabase;

    private User $mahasiswa;
    private Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mahasiswa = User::factory()->create([
            'name' => 'Lucas Pardede',
            'email' => 'lucas.pardede@del.ac.id',
            'nim' => '12S24015',
            'role' => 'mahasiswa',
        ]);

        $kategori = Kategori::create([
            'kode_kategori' => 'IF-TEST',
            'nama_kategori' => 'Informatika Uji',
        ]);

        $this->buku = Buku::create([
            'isbn' => '978-602-00-0001-1',
            'judul' => 'Buku Uji SOAP Perpustakaan',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Del',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);
    }

    public function test_wsdl_contract_is_valid_and_accessible(): void
    {
        $response = $this->get('/soap/sipus.wsdl');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('CheckClearanceStatus', $content);
        $this->assertStringContainsString('CheckClearanceStatusRequest', $content);
        $this->assertStringContainsString('CheckClearanceStatusResponse', $content);
        $this->assertStringContainsString('nim_peminjam', $content);
        $this->assertStringContainsString('active_loans', $content);
        $this->assertStringContainsString('is_cleared', $content);
        $this->assertStringContainsString('LibraryPortType', $content);
        $this->assertStringContainsString('LibrarySoapBinding', $content);
        $this->assertStringContainsString('LibrarySoapService', $content);

        // Validasi well-formed XML
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml, 'WSDL harus berupa XML yang valid.');
    }

    public function test_soap_successful_check_cleared_true_when_no_active_loans(): void
    {
        $requestXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S24015</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $requestXml);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');

        $content = $response->getContent();
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml);

        $this->assertStringContainsString('<m:nim_peminjam>12S24015</m:nim_peminjam>', $content);
        $this->assertStringContainsString('<m:active_loans>0</m:active_loans>', $content);
        $this->assertStringContainsString('<m:is_cleared>true</m:is_cleared>', $content);
        $this->assertStringContainsString('SIPUS-Del Legacy SOAP Engine v1.0', $content);
    }

    public function test_soap_successful_check_cleared_false_when_has_active_loans(): void
    {
        Peminjaman::create([
            'user_id' => $this->mahasiswa->id,
            'buku_id' => $this->buku->id,
            'nim_peminjam' => $this->mahasiswa->nim,
            'nama_peminjam' => $this->mahasiswa->name,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => Peminjaman::STATUS_DIPINJAM,
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        $requestXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S24015</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $requestXml);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('<m:nim_peminjam>12S24015</m:nim_peminjam>', $content);
        $this->assertStringContainsString('<m:active_loans>1</m:active_loans>', $content);
        $this->assertStringContainsString('<m:is_cleared>false</m:is_cleared>', $content);
    }

    public function test_soap_fault_when_body_is_empty(): void
    {
        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], '');

        $response->assertStatus(500);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $content);
        $this->assertStringContainsString('Body XML SOAP tidak boleh kosong', $content);
    }

    public function test_soap_fault_when_xml_is_malformed(): void
    {
        $malformedXml = '<soap:Envelope><soap:Body><broken>';

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $malformedXml);

        $response->assertStatus(500);
        $content = $response->getContent();
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $content);
        $this->assertStringContainsString('Format XML tidak valid', $content);
    }

    public function test_soap_fault_when_nim_element_is_missing(): void
    {
        $requestXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <CheckClearanceStatusRequest>
            <other_tag>12345</other_tag>
        </CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $requestXml);

        $response->assertStatus(500);
        $content = $response->getContent();
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $content);
        $this->assertStringContainsString('elemen &lt;nim_peminjam&gt; tidak ditemukan', $content);
    }

    public function test_soap_fault_when_nim_is_empty(): void
    {
        $requestXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <CheckClearanceStatusRequest>
            <nim_peminjam></nim_peminjam>
        </CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $requestXml);

        $response->assertStatus(500);
        $content = $response->getContent();
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $content);
        $this->assertStringContainsString('Nilai elemen &lt;nim_peminjam&gt; tidak boleh kosong', $content);
    }

    public function test_soap_fault_when_nim_is_not_registered(): void
    {
        $requestXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <CheckClearanceStatusRequest>
            <nim_peminjam>99S99999</nim_peminjam>
        </CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $requestXml);

        $response->assertStatus(500);
        $content = $response->getContent();
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $content);
        $this->assertStringContainsString("NIM '99S99999' tidak terdaftar", $content);
    }
}
