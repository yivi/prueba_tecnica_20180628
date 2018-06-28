<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\TrovitService\TrovitService;
use Symfony\Component\HttpFoundation\JsonResponse;

class TrovitRestController {

	/**
	 * @param \App\TrovitService\TrovitService $trovit_service
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getTrovitAds( TrovitService $trovit_service ) {
		try {
			$data = $trovit_service->getAds( TrovitService::SORT_ID );
		} catch ( \Exception $e ) {
			return new JsonResponse( [ 'status' => 'KO', 'error' => $e->getMessage() ] );
		}

		return new JsonResponse(
			[
				'status' => 'OK',
				'data'   => $data,
			]
		);
	}
}