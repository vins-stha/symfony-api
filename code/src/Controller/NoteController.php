<?php

namespace App\Controller;

use App\Entity\Note;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


class NoteController extends AbstractController
{
  /**
   * @Route("/api/v1/notes", name="list_note", methods={"GET"})
   * @OA\Response(
   *   response=200,
   *   description="successful",
   * @OA\JsonContent(
   *   type="array",
   *   @OA\Items(ref=@Model(type=Note::class))
   * )
   * )
   * @OA\Tag(name="Notes list")
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $notes = $this->getDoctrine()->getRepository(Note::class)->findAll();

    return $this->json($notes);
  }

  /**
   *
   * @Route("/api/v1/notes/{id}", name="detailed_note", methods={"GET"})
   * @OA\Post(
   *   description="Add a new note"
   * )
   * @OA\RequestBody(
   *   description="Json Payload",
   *   @OA\MediaType(
   *   mediaType="application/json"
   * )
   * )
   * @OA\Response(
   *   response=201,
   *   description="Note added successfully",
   *
   * @OA\JsonContent(
   *   type="array",
   *   @OA\Items(ref=@Model(type=Note::class))
   * )
   * )
   * @OA\Tag(name="Single note view")
   * @param Request $request
   * @return JsonResponse
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
   * @Route("/api/v1/notes", name="create_note", methods={"POST"})
   * @OA\Response(
   *   response=200,
   *   description="successful",
   * @OA\JsonContent(
   *   type="array",
   *   @OA\Items(ref=@Model(type=Note::class))
   * )
   * )
   * @OA\Tag(name="Add new note")
   * @param Request $request
   * @return JsonResponse
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

    return $this->json($message);;
  }

  /**
   *
   * @Route("/api/v1/notes/{id}", name="update_note", methods={"PUT"})
   * @OA\Response(
   *   response=200,
   *   description="successful",
   * @OA\JsonContent(
   *   type="array",
   *   @OA\Items(ref=@Model(type=Note::class))
   * )
   * )
   * @OA\Tag(name="Edit note")
   * @param Request $request
   * @return JsonResponse
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
   * @OA\Response(
   *   response=200,
   *   description="delete successful",
   * @OA\JsonContent(
   *   type="array",
   * )
   * )
   * @OA\Tag(name="Delete note")
   * @param Request $request
   * @return JsonResponse
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
          "message" => "Deleted note id # $id successfully",
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
