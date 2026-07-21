<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case Checkbox = 'checkbox';
    case Text = 'text';
    case FileUpload = 'file_upload';
}
