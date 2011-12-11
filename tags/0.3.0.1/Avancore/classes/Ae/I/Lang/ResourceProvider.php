<?php

interface Ae_I_Lang_ResourceProvider {
	
	/**
	 * Returns has used for by resource content caching (it should change if content has changed)
	 * 
	 * @param string $langId 'ru', 'en', 'de' and so on...
	 * @return string 
	 */
	function getLangHash($langId);
	
	/**
	 * Returns array with language strings.
	 * 
	 * @param string $langId 'ru', 'en', 'de' and so on...
	 * @return array
	 */
	function getLangData($langId);
	
}