<?php


namespace App\Controller;


use App\Entity\Note;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

class NoteCreateFactory
{
  public static function createNote(string $title, string $text):Note
  {
    $note = new Note();

    $note->setTitle($title);
    $note->setText($text ?? "");

    return $note;
  }

  public static function validateNote($note)
  {
    $validator = Validation::createValidator();
    $violations = $validator->validate($note->getTitle(),[
        new Length(['min' => 3]),
        new NotBlank(),
    ]);

    if(count($violations) > 0 )
      $violations = "Validation Error: Title should be at least 3 characters long and NOT Blank";

    return $violations;

  }
}