<?php

namespace App\Http\Resources;

use App\Enums\ResultDisplayMode;
use App\Enums\UserRole;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class AssessmentResultResource extends JsonApiResource
{
    /**
     * @throws InvalidEnumMemberException
     */
    public function toAttributes(Request $request): array
    {
        $userLoaded = $this->relationLoaded('user');

        $response = [
            'assessmentId' => $this->assessment_id,
            'name' => $this->assessment->name,
            'thumbnail' => $this->assessment->thumbnail,
            'startedAt' => $this->created_at,
            'user' => $this->when($userLoaded, UserResource::make($this->user)),
            'marked' => $this->marked,
            'requiredMark' => $this->assessment->required_mark,
            'fromOwner' => false,
        ];

        if (auth()->user()->role === UserRole::Admin || $this->assessment->created_by === auth()->user()->id) {
            $response['displayMode'] = ResultDisplayMode::getKey(ResultDisplayMode::DisplayMarkAndAnswers);
            $response['fromOwner'] = true;
        } elseif ($this->assessment->result_display_mode !== null) {
            $response['displayMode'] = ResultDisplayMode::getKey($this->assessment->result_display_mode);
        }

        switch ($this->assessment->result_display_mode) {
            case ResultDisplayMode::DisplayMarkOnly:
            case ResultDisplayMode::DisplayMarkAndAnswers:
                $response['score'] = $this->total_marks;
                $response['totalMarks'] = $this->assessment->total_marks;
                break;
            default:
                break;
        }

        return $response;
    }
}
