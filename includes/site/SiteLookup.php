<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Site;

/**
 * Interface to retrieve Site objects, for implementation by service classes.
 *
 * Default implementation is DBSiteStore.
 *
 * @since 1.25
 * @ingroup Site
 */
interface SiteLookup {

	/**
	 * Return the site with provided global ID, or null if there is no such site.
	 *
	 * @since 1.25
	 * @param string $globalId
	 * @return Site|null
	 */
	public function getSite( $globalId );

	/**
	 * Return a list of all sites.
	 *
	 * @since 1.25
	 * @return SiteList
	 */
	public function getSites();

}

/** @deprecated class alias since 1.42 */
class_alias( SiteLookup::class, 'SiteLookup' );
