<?php

namespace App\Test\Controller;

use App\Entity\Organismo;
use App\Repository\OrganismoRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrganismoControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private OrganismoRepository $repository;
    private string $path = '/organismo/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Organismo::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Organismo index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'organismo[Nombre]' => 'Testing',
            'organismo[Domicilio]' => 'Testing',
            'organismo[Telefono]' => 'Testing',
        ]);

        self::assertResponseRedirects('/organismo/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Organismo();
        $fixture->setNombre('My Title');
        $fixture->setDomicilio('My Title');
        $fixture->setTelefono('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Organismo');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Organismo();
        $fixture->setNombre('My Title');
        $fixture->setDomicilio('My Title');
        $fixture->setTelefono('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'organismo[Nombre]' => 'Something New',
            'organismo[Domicilio]' => 'Something New',
            'organismo[Telefono]' => 'Something New',
        ]);

        self::assertResponseRedirects('/organismo/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNombre());
        self::assertSame('Something New', $fixture[0]->getDomicilio());
        self::assertSame('Something New', $fixture[0]->getTelefono());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Organismo();
        $fixture->setNombre('My Title');
        $fixture->setDomicilio('My Title');
        $fixture->setTelefono('My Title');

        $this->repository->add($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/organismo/');
    }
}
