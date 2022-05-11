<?php

namespace App\Controller;

use App\Entity\Note;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class NoteController extends AbstractController
{
  /**
   *
   * @Route("/api/v1/notes", name="list_note", methods={"GET"})
   *
   */
  public function index(Request $request): JsonResponse
  {
    $notes = $this->getDoctrine()->getRepository(Note::class)->findAll();

    return $this->json($notes);
  }

  /**
   *
   * @Route("/api/v1/notes/{id}", name="detailed_note", methods={"GET"})
   *
   */
  public function note(Request $request): JsonResponse
  {
    $id = intval($request->get('id'));

    $note = $this
        ->getDoctrine()
        ->getRepository(Note::class)
        ->findOneBy(["id" => $id]);

    if (!$note) {
      throw $this->createNotFoundException(
          'No note found for id ' . $id
      );
    }

    $data = [
        'id' => $note->getId(),
        'title' => $note->getTitle(),
        'text' => $note->getText()
    ];

    return $this->json($data);
  }

  /**
   *
   * @Route("/api/v1/notes/add", name="create_note", methods={"POST"})
   *
   */
  public function create(Request $request): JsonResponse
  {
    if ($this->is_Json($request->getContent())) {

      // decode to array
      $data = json_decode($request->getContent(), true);

      $title = (array_key_exists("title", $data)) ? $data['title'] : "";
      $text = (array_key_exists("text", $data)) ? $data['text'] : "";

    } else {
      $title = $request->get('title') ?? "";
      $text = $request->get('text') ?? "";
    }

    $note = NoteCreateFactory::createNote($title, $text);

    $errors = NoteCreateFactory::validateNote($note);

    if (strlen($errors) > 0) {
      $message = [
          'message' => $errors,
          'code' => Response::HTTP_BAD_REQUEST
      ];
    } else {
      $em = $this->getDoctrine()->getManager();
      $em->persist($note);
      $em->flush();
      $message = [
          'message' => "Note created successfully",
          'code' => Response::HTTP_CREATED,
          'note' => $this->getNote($note)
      ];
    }

    return $this->json($message);
;
  }

  /**
   *
   * @Route("/api/v1/notes/{id}", name="update_note", methods={"PUT"})
   *
   */
  public function update(Request $request, ValidatorInterface $validator): JsonResponse
  {
    $id = intval($request->get('id'));

    $note = $this
        ->getDoctrine()
        ->getRepository(Note::class)
        ->findOneBy(["id" => $id]);

    if (!$note) {
      return $this->json('No note found for id' . $id, Response::HTTP_NOT_FOUND);
    }

    if ($this->is_Json($request->getContent())) {

      // decode to array
      $data = json_decode($request->getContent(), true);

      $title = (array_key_exists("title", $data)) ? $data['title'] : $note->getTitle();
      $text = (array_key_exists("text", $data)) ? $data['text'] : $note->getText();
    } else {

      $title = $request->get('title') ? $request->get('title') : $note->getTitle();
      $text = $request->get('text') ? $request->get('text') : $note->getText();
    }

    $note->setTitle($title);
    $note->setText($text);

    $errors = NoteCreateFactory::validateNote($note);

    if (strlen($errors) > 0) {
      $message = [
          'message' => $errors,
          'code' => Response::HTTP_BAD_REQUEST
      ];
    } else {
      $em = $this->getDoctrine()->getManager();
      $em->persist($note);
      $em->flush();
      $data = $this->getNote($note);
      $message = [
          "message" => "Note $id updated successfully",
          "code" => Response::HTTP_OK,
          "note" => $data
      ];
    }
//    $response = new  Response($this->json($message));
    return $this->json($message);

  }

  /**
   *
   * @Route("/api/v1/notes/{id}", name="delete_note", methods={"DELETE"})
   *
   */
  public function delete(Request $request): JsonResponse
  {
    $id = intval($request->get('id'));

    $note = $this
        ->getDoctrine()
        ->getRepository(Note::class)
        ->findOneBy(["id" => $id]);

    if (!$note) {
      $message = [
          "message" => 'No note found for id ' . $id,
          "code" => Response::HTTP_NOT_FOUND
      ];
    }
    try {
      $em = $this->getDoctrine()->getManager();
      $em->remove($note);
      $em->flush();
      $message = [
          "message" =>"Deleted note id # $id successfully",
          "code" => Response::HTTP_OK
      ];
    } catch (\Exception $e) {
      $message = [
          "message" => 'Something went wrong ',
          "code" => $e
      ];
    }

    return $this->json($message);
  }


  public function is_Json($object)
  {
    return (is_null(json_decode($object))) ? FALSE : TRUE;
  }

  protected function getNote($note)
  {
    $data = [
        "id" => $note->getId(),
        "title" => $note->getTitle(),
        "text" => $note->getText(),
        "created_time" => $note->getCreatedTime()
    ];
    return $data;
  }

}
