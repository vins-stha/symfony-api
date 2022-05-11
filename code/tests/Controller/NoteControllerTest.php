<?php

use GuzzleHttp\Client;

class NoteControllerTest extends PHPUnit\Framework\TestCase
{

  const BASEURI = "http://host.docker.internal:8001/api/v1/notes";

  public function testCreate()
  {
    $client = new Client();
    $title = "foobar-title";
    $text = "foobar-text";
    $data = array(
        'title' => $title,
        'text' => $text,
    );
    $response = $client->request('POST', self::BASEURI . '/add', [
        'body' => json_encode($data),
        'verify' => false,
        'debug' => false
    ]);

    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);

    $this->assertArrayHasKey("message", $results);
    $this->assertArrayHasKey("code", $results);
    $this->assertArrayHasKey("note", $results);

    $this->assertEquals(201, $results['code']);
    $this->assertEquals("Note created successfully", $results['message']);

    $note = $results['note'];
    $this->assertArrayHasKey("title", $note);
    $this->assertArrayHasKey("text", $note);
    $this->assertArrayHasKey("id", $note);

    $this->assertEquals("foobar-title", $note['title']);
    $this->assertEquals("foobar-text", $note['text']);
    $this->assertIsNumeric($note['id']);

    $this->deleteNote($note['id']);

  }

  public function testGetSingleNote()
  {
    $client = new Client();
    $title = "foobar-title";
    $text = "foobar-text";
    $data = array(
        'title' => $title,
        'text' => $text,
    );
    $response = $client->request('POST', self::BASEURI . '/add', [
        'body' => json_encode($data),
        'verify' => false,
        'debug' => false
    ]);

    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);

    $id = $results['note']['id'];
    $response = $client->request('GET', self::BASEURI . "/$id", [
        'verify' => false,
        'debug' => false
    ]);

    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);
    $this->assertArrayHasKey("id", $results);
    $this->assertIsNumeric($results['id']);
    $this->assertArrayHasKey("title", $results);
    $this->assertArrayHasKey("text", $results);
    $this->assertEquals(200, $response->getStatusCode());

    // clean up
    $this->deleteNote($id);

  }

  public function testGetNotes()
  {
    $client = new Client();
    $ids = array();

    for ($i = 1; $i <= 2; $i++) {
      $title = "foobar-title-" . $i;
      $text = "foobar-text-" . $i;
      $data = array(
          'title' => $title,
          'text' => $text,
      );

      // create notes
      $response = $client->request('POST', self::BASEURI . '/add', [
          'body' => json_encode($data),
          'verify' => false,
          'debug' => false
      ]);

      $stringResponse = (string)$response->getBody()->getContents();

      $results = json_decode($stringResponse, true);

      $ids[] = $results['note']['id'];
    }

    $response = $client->request('GET', self::BASEURI, [
        'verify' => false,
        'debug' => false
    ]);
    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);

    $this->assertFalse(empty($results));
    $this->assertGreaterThanOrEqual(count($ids), count($results));

    for ($i = $j = 0; $i < count($results); $i++) {

      if (in_array($results[$i]['id'], $ids)) {

        $j = $j + 1;

        $this->assertArrayHasKey('title', $results[$i]);
        $this->assertEquals("foobar-title-" . $j, $results[$i]['title']);
        $this->assertArrayHasKey('text', $results[$i]);
        $this->assertEquals("foobar-text-" . $j, $results[$i]['text']);

        // clean up
        $this->deleteNote($results[$i]['id']);
      }
    }
  }

  public function testEditNote()
  {
    $client = new Client();
    $title = "foobar-title";
    $text = "foobar-text";
    $data = array(
        'title' => $title,
        'text' => $text,
    );
    $response = $client->request('POST', self::BASEURI . '/add', [
        'body' => json_encode($data),
        'verify' => false,
        'debug' => false
    ]);

    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);

    $id = $results['note']['id'];

    $data = array(
        'title' => "updated title",
        'text' => "updated text",
    );

    $response = $client->request('PUT', self::BASEURI . "/$id", [
        'body' => json_encode($data),
        'verify' => false,
        'debug' => false
    ]);

    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);
    $this->assertArrayHasKey("message", $results);
    $this->assertArrayHasKey("code", $results);
    $this->assertArrayHasKey("note", $results);

    $this->assertEquals(200, $results['code']);
    $this->assertEquals("Note $id updated successfully", $results['message']);

    $note = $results['note'];
    $this->assertArrayHasKey("title", $note);
    $this->assertArrayHasKey("text", $note);
    $this->assertArrayHasKey("id", $note);

    $this->assertEquals("updated title", $note['title']);
    $this->assertEquals("updated text", $note['text']);

    // clean up
    $this->deleteNote($note['id']);
  }

  public function testDeleteNote()
  {
    $client = new Client();
    $title = "foobar-title";
    $text = "foobar-text";
    $data = array(
        'title' => $title,
        'text' => $text,
    );
    $response = $client->request('POST', self::BASEURI . '/add', [
        'body' => json_encode($data),
        'verify' => false,
        'debug' => false
    ]);

    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);

    $id = $results['note']['id'];

    $response = $client->request('DELETE', self::BASEURI . "/$id", [
        'verify' => false,
        'debug' => false
    ]);

    $stringResponse = (string)$response->getBody()->getContents();

    $results = json_decode($stringResponse, true);

    $this->assertArrayHasKey("message", $results);
    $this->assertArrayHasKey("code", $results);

    $this->assertEquals(200, $results['code']);
    $this->assertEquals("Deleted note id # $id successfully", $results['message']);
  }

  public function deleteNote($id)
  {
    $client = new Client();

    $client->request('DELETE', self::BASEURI . "/$id", [
        'verify' => false,
        'debug' => false
    ]);
  }
}
