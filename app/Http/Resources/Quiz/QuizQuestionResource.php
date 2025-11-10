<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'=>$this->id,
            'question'=>$this->question,
            'answers'=>$this->answers->map(fn($a)=>['text'=>$a->answer_text,'key'=>$a->result_key]),
        ];
    }
}