<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\Joomla;

trait ArticleTrait
{
	/**
	 * Creates an article in the database and returns the article data
	 * as array including the id of the new article.
	 */
	public function createArticle(?array $data = null): array
	{
		$article = [
			'catid'        => 2,
			'title'        => 'Test article',
			'alias'        => 'test-article',
			'featured'     => 1,
			'state'        => 1,
			'access'       => 1,
			'language'     => '*',
			'introtext'    => '',
			'fulltext'     => '',
			'images'       => '',
			'urls'         => '',
			'attribs'      => '',
			'metakey'      => '',
			'metadesc'     => '',
			'metadata'     => '',
			'publish_up'   => (new \DateTime())->format('Y-m-d H:i:s'),
			'publish_down' => null,
			'created'      => (new \DateTime())->format('Y-m-d H:i:s'),
			'modified'     => (new \DateTime())->format('Y-m-d H:i:s'),
			'created_by'   => $this->grabFromDatabase('users', 'id', ['username' => 'admin']),
		];

		if (\is_array($data)) {
			$article = array_merge($article, $data);
		}

		$article['id'] = $this->haveInDatabase('content', $article);
		$this->haveInDatabase('content_frontpage', ['content_id' => $article['id'], 'ordering' => 1]);

		$this->haveInDatabase('workflow_associations', ['item_id' => $article['id'], 'stage_id' => 1, 'extension' => 'com_content.article']);

		return $article;
	}
}
