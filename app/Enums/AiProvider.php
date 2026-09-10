<?php

namespace App\Enums;

enum AiProvider: string
{
    case OpenAi = 'openai';
    case Gemini = 'gemini';
}
