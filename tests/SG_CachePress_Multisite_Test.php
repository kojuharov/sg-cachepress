<?php

use Brain\Monkey\Functions;

/**
 * @coversDefaultClass SG_CachePress_Multisite
 */
class SG_CachePress_Multisite_Test extends SG_CachePress_TestCase {

	/**
	 * @coversDefaultClass
	 */
	public function test_construct() {

		Functions\expect( 'is_multisite' )
			->once()
			->andReturn( true );

		Functions\expect( 'is_network_admin' )
			->once()
			->andReturn( true );

		Functions\when( 'esc_html__' )
			->returnArg();

		$object = new SG_CachePress_Multisite();

		$this->assertTrue( has_filter(
			'bulk_actions-sites-network',
			'SG_CachePress_Multisite->bulk_actions()'
		) );

		$this->assertTrue( has_filter(
			'handle_network_bulk_actions-sites-network',
			'SG_CachePress_Multisite->handle_network_bulk_actions()'
		) );

		$this->assertTrue( has_action(
			'network_admin_notices',
			'SG_CachePress_Multisite->network_admin_notices()'
		) );

		return $object;
	}

	/**
	 * @covers  bulk_actions()
	 * @depends test_construct
	 *
	 * @param SG_CachePress_Multisite $object
	 */
	public function test_bulk_actions( SG_CachePress_Multisite $object ) {

		$this->assertCount( 5, $object->bulk_actions( [] ) );
	}

	/**
	 * @covers  handle_network_bulk_actions()
	 * @depends test_construct
	 *
	 * @param SG_CachePress_Multisite $object
	 */
	public function test_handle_network_bulk_actions( SG_CachePress_Multisite $object ) {

		$site_ids = [ 1, 2, 3 ];
		$count    = count( $site_ids );

		Functions\expect( 'remove_query_arg' )
			->twice()
			->andReturn( 'https://example.com' );
		Functions\expect( 'switch_to_blog' )
			->times( $count );
		Functions\expect( 'sg_cachepress_purge_cache' )
			->times( $count );
		Functions\expect( 'restore_current_blog' )
			->times( $count );
		Functions\expect( 'add_query_arg' )
			->once()
			->with( 'sg-cache-purged', $count, 'https://example.com' );

		$object->handle_network_bulk_actions(
			'https://example.com',
			'sg-purge-cache',
			$site_ids
		);
	}
}