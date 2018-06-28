<?php

namespace App\Controller;

use App\TrovitService\TrovitService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TrovitTableController extends Controller {

	public function trovitTableAction( TrovitService $trovitService, Request $request ) {

		$sortOrder = $request->get( 'sort', TrovitService::SORT_ID );

		if ( ! in_array( $sortOrder, TrovitService::AVAILABLE_SORTS ) ) {
			$sortOrder = TrovitService::SORT_ID;
		}

		try {
			$data = $trovitService->getAds( $sortOrder );
		} catch ( \Exception $e ) {
			$data = null;
		}

		return $this->render( 'trovit_table.html.twig', [ 'advertisements' => $data ] );

	}

}