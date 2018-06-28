<?php

namespace App\TrovitService;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class TrovitService {

	/**
	 * @var \GuzzleHttp\ClientInterface
	 */
	private $client;
	/**
	 * @var \Symfony\Component\Cache\Adapter\AdapterInterface
	 */
	private $cache;

	const SORT_ID    = 'id';

	const SORT_TITLE = 'title';

	const SORT_LINK  = 'url';

	const SORT_CITY  = 'city';

	const AVAILABLE_SORTS
	                 = [
			self::SORT_ID,
			self::SORT_TITLE,
			self::SORT_LINK,
			self::SORT_CITY,
		];


	/**
	 * TrovitService constructor.
	 *
	 * @param \GuzzleHttp\ClientInterface                       $client
	 * @param \Symfony\Component\Cache\Adapter\AdapterInterface $cache
	 */
	public function __construct( ClientInterface $client, AdapterInterface $cache ) {
		$this->client = $client;
		$this->cache  = $cache;
	}

	/**
	 * @param string $country
	 * @param string $sortBy
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getAds( $sortBy = self::SORT_ID, $country = 'Ireland' ): array {

		$cache_key = "trovit_" . $country;

		// Check that it is a supported sorting method.
		if ( ! in_array( $sortBy, self::AVAILABLE_SORTS ) ) {
			throw new \Exception( 'Invalid Sort: ' . $sortBy );
		}

		try {
			// check the cache first, just in case
			$cachedItem = $this->cache->getItem( $cache_key );
		} catch ( InvalidArgumentException $e ) {
			throw new \Exception( 'Cache problems:' . $e->getMessage() );
		}

		// if we do not have it cached, let's hit the service again.
		if ( ! $cachedItem->isHit() ) {
			try {
				$apiResponse = $this->client->request( "GET", "/trovit-Ireland.xml" );

				if ( $apiResponse->getStatusCode() != 200 ) {
					throw  new \Exception( 'Invalid HTTP Response: ' . $apiResponse->getStatusCode() );
				}

				$response = new \SimpleXMLElement( $apiResponse->getBody()->getContents() );

				// Since cache wasn't hit, let's create a cacheItem for future use
				$cachedItem->set( $this->xmlToArray( $response ) );
				$this->cache->save( $cachedItem );

			} catch ( GuzzleException $e ) {
				throw new \Exception( 'Connection troubles:' . $e->getMessage() );
			}
		}

		/** @var array $resultArray */
		// One way or another, we should have a cache item ready for use.
		$resultArray = $cachedItem->get();

		// apply sort to the resulting array.
		usort( $resultArray, function ( $a, $b ) use ( $sortBy ) {
			return $a[ $sortBy ] <=> $b[ $sortBy ];
		} );

		return $resultArray;
	}

	/**
	 * @param \SimpleXMLElement $response
	 *
	 * @return array
	 */
	protected function xmlToArray( \SimpleXMLElement $response ): array {

		$results = [];

		foreach ( $response->ad as $ad ) {

			$newAd     = [
				'id'            => (int) $ad->id,
				'title'         => (string) $ad->title,
				'url'           => (string) $ad->url,
				'city'          => (string) $ad->city,
				'picture_url'   => (string) $ad->pictures->picture[0]->picture_url,
				'picture_title' => (string) $ad->pictures->picture[0]->picture_title,
			];
			$results[] = $newAd;

		}

		return $results;
	}

}