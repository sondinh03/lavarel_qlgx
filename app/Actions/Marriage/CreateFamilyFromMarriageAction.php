<?php



namespace App\Actions\Marriage;



use App\DTOs\MarriageProcessResult;

use App\Models\Marriage;

use App\Services\MarriageService;

use InvalidArgumentException;



/** @deprecated Use MarriageService::processValidMarriage() directly */

class CreateFamilyFromMarriageAction

{

    public function __construct(private MarriageService $marriageService) {}



    public function handle(Marriage $marriage): MarriageProcessResult

    {

        if ($marriage->status !== Marriage::STATUS_VALID) {

            throw new InvalidArgumentException('Chỉ tạo gia đình khi hôn phối hợp lệ.');

        }



        return $this->marriageService->processValidMarriage($marriage);

    }

}

