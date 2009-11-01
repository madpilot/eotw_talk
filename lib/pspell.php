<?php
class PSpell {
  protected $options = array('mode' => PSPELL_FAST, 'jargon' => '', 'encoding' => '', 'spelling' => '');
	/**
	 * Spellchecks an array of words.
	 *
	 * @param {String} $lang Language code like sv or en.
	 * @param {Array} $words Array of words to spellcheck.
	 * @return {Array} Array of misspelled words.
	 */
	public function &checkWords($lang, $words) {
		$plink = $this->getPLink($lang);

		$outWords = array();
		foreach ($words as $word) {
			if (!pspell_check($plink, trim($word)))
				$outWords[] = utf8_encode($word);
		}

		return $outWords;
	}

	/**
	 * Returns suggestions of for a specific word.
	 *
	 * @param {String} $lang Language code like sv or en.
	 * @param {String} $word Specific word to get suggestions for.
	 * @return {Array} Array of suggestions for the specified word.
	 */
	public function &getSuggestions($lang, $word) {
		$words = pspell_suggest($this->getPLink($lang), $word);

		for ($i=0; $i<count($words); $i++)
			$words[$i] = utf8_encode($words[$i]);

		return $words;
	}

	/**
	 * Opens a link for pspell.
	 */
	private function &getPLink($lang) {
		// Check for native PSpell support
		if (!function_exists("pspell_new"))
			throw new Exception("PSpell support not found in PHP installation.");

		// Setup PSpell link
		$plink = pspell_new(
			$lang,
			$this->options['spelling'],
			$this->options['jargon'],
			$this->options['encoding'],
			$this->options['mode']
		);

		if (!$plink)
			throw new Exception("No PSpell link found opened.");

		return $plink;
	}
}

?>
