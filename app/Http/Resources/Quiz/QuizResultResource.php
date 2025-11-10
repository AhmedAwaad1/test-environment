<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizResultResource extends JsonResource {
    public function toArray($request): array {
        return ['result_key'=>$this->result_key,'result_text'=>$this->result_text];
    }
}