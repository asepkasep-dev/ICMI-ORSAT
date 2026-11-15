<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_accessible_without_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('ICMI Kecamatan')
            ->assertSee('Sambutan Ketua');
    }

    public function test_all_public_pages_are_accessible(): void
    {
        $pages = [
            '/profil-organisasi' => 'Profil Organisasi',
            '/struktur-kepengurusan' => 'Struktur Kepengurusan',
            '/berita' => 'Berita',
            '/galeri' => 'Galeri Foto',
            '/kontak' => 'Kontak',
        ];

        foreach ($pages as $url => $title) {
            $this->get($url)->assertOk()->assertSee($title);
        }
    }

    public function test_public_pages_do_not_expose_cms_without_login(): void
    {
        $this->get('/berita')->assertOk()->assertDontSee('Kewenangan Writer');
    }
}