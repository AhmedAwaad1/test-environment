<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use App\Models\QuizResult;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'question' => 'كيف يبدأ لون جسمك يتغيّر عادةً؟',
                'answers' => [
                    ['text' => 'أ) بعد الشمس أو الحرارة', 'result_key' => 'A'],
                    ['text' => 'ب) بعد توتر أو ضغط نفسي', 'result_key' => 'B'],
                    ['text' => 'ج) بدون سبب واضح، يغمق بالتدريج', 'result_key' => 'C'],
                ],
            ],
            [
                'question' => 'تحسين المنطقة الغامقة ملمسها كيف؟',
                'answers' => [
                    ['text' => 'أ) خشن شوي', 'result_key' => 'A'],
                    ['text' => 'ب) ناعم بس غامق', 'result_key' => 'B'],
                    ['text' => 'ج) عادي جدًا', 'result_key' => 'C'],
                ],
            ],
            [
                'question' => 'هل بشرتك ترجع تغمق بعد أي تفتيح؟',
                'answers' => [
                    ['text' => 'أ) دايمًا', 'result_key' => 'A'],
                    ['text' => 'ب) أحيانًا', 'result_key' => 'B'],
                    ['text' => 'ج) نادرًا', 'result_key' => 'C'],
                ],
            ],
            [
                'question' => 'هل تحسين لونك مو غامق، لكن “مطفّي” بدون لمعة؟',
                'answers' => [
                    ['text' => 'أ) إيه', 'result_key' => 'A'],
                    ['text' => 'ب) لا', 'result_key' => 'B'],
                ],
            ],
            [
                'question' => 'متى تحسين لون جسمك أغمق؟',
                'answers' => [
                    ['text' => 'أ) بالصيف', 'result_key' => 'A'],
                    ['text' => 'ب) بالدورة أو التوتر', 'result_key' => 'B'],
                    ['text' => 'ج) طول الوقت ثابت', 'result_key' => 'C'],
                ],
            ],
            [
                'question' => 'تحسين كل ما قشّرت أو فتّحت، يرجع أغمق؟',
                'answers' => [
                    ['text' => 'أ) دايمًا', 'result_key' => 'A'],
                    ['text' => 'ب) أحيانًا', 'result_key' => 'B'],
                    ['text' => 'ج) لا', 'result_key' => 'C'],
                ],
            ],
        ];

        $results = [
            'A' => 'تصبغ حراري – ناتج عن نشاط الميلانين بسبب حرارة داخلية أو خارجية.',
            'B' => 'تصبغ هرموني – الجسم يفرز ميلانين لحماية نفسه من اضطراب الهرمونات.',
            'C' => 'تصبغ تراكمي – الميلانين عندك ما يتنظم تلقائيًا.',
            'A1' => 'تصبغ خلايا متعبة – يحتاج تقشير وتجديد.',
            'B1' => 'تصبغ عميق – ناتج من خلل في توزيع الميلانين.',
            'C1' => 'تصبغ ناشط – الميلانين متفاعل بزيادة ويحتاج موازنة.',
            'A2' => 'عندك اختلال بتنظيم الميلانين. ما ينفع تفتيح سطحي.',
            'B2' => 'عندك نشاط ميلانين موسمي. تحتاجين توازن ثابت.',
            'C2' => 'ممتاز! بس تحتاجين تحافظين بالترطيب العميق.',
            'A3' => 'هذا مو تصبغ، هذا فقدان توازن ميلانين. الحل مو تفتيح، بل تحفيز معتدل.',
            'B3' => 'جسمك غالبًا متوازن، حافظي عليه بتنظيم ناعم مستمر.',
            'A4' => 'تصبغ حراري مؤقت.',
            'B4' => 'تصبغ هرموني.',
            'C4' => 'تصبغ متراكم – الميلانين ثابت ويحتاج إعادة تهيئة.',
            'A5' => 'تصبغ جذري – لازم تنظيم إنزيمات الميلانين (وهذا شغل عمق).',
            'B5' => 'تصبغ سطحي جزئي – يحتاج موازنة بسيطة.',
            'C5' => 'تصبغ بسيط – الترطيب والوقاية كافيين.',
        ];

        foreach ($results as $key => $text) {
            QuizResult::create([
                'result_key' => $key,
                'result_text' => $text
            ]);
        }

        foreach ($questions as $q) {
            $question = QuizQuestion::create(['question' => $q['question']]);
            foreach ($q['answers'] as $a) {
                QuizAnswer::create([
                    'quiz_question_id' => $question->id,
                    'answer_text' => $a['text'],
                    'result_key' => $a['result_key']
                ]);
            }
        }
    }
}
