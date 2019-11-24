<?php

namespace Molkobain\iTop\Extension\Pixbox\Portal\Controller;

use Combodo\iTop\Portal\Controller\AbstractController;
use Combodo\iTop\Portal\Helper\ApplicationHelper;
use Combodo\iTop\Portal\Helper\ScopeValidatorHelper;
use Combodo\iTop\Portal\Helper\UrlGenerator;
use DBObjectSearch;
use DBObjectSet;
use MetaModel;
use Molkobain\iTop\Extension\Pixbox\Portal\Brick\ReaderBrick;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserRights;

/**
 * Class ReaderBrickController
 *
 * @package Molkobain\iTop\Extension\Pixbox\Portal\Controller
 */
class ReaderBrickController extends AbstractController
{
	/**
	 * Method for the brick's page
	 *
	 * @param Request     $oRequest
	 * @param Application $oApp
	 * @param             $sBrickId
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function DisplayAction(Request $oRequest, Application $oApp, $sBrickId)
	{
		// Retrieving brick instance
		$oBrick = ApplicationHelper::GetLoadedBrickFromId($oApp, $sBrickId);

		// Structured  data passed to the template
		$aData = array(
			'sBrickId' => $sBrickId,
		);

		// Finally, return a Response object
		return $oApp['twig']->render($oBrick->GetPageTemplatePath(), $aData);
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $oRequest
	 * @param \Silex\Application                        $oApp
	 *
	 * @throws \CoreException
	 * @throws \OQLException
	 */
	public function GetNextPosts(Request $oRequest, Application $oApp)
	{
		// Retrieve current contact
		$oContact = UserRights::GetContactObject();
		if($oContact === null)
		{
			$oApp->abort(401);
		}

		/** @var ScopeValidatorHelper $oScopeHelper */
		$oScopeHelper = $oApp['scope_validator'];
		/** @var \Combodo\iTop\Portal\Helper\UrlGenerator $oUrlGenerator */
		$oUrlGenerator = $oApp['url_generator'];

		$aData = array(
			'count' => 0,
			'items' => array(),
		);

		// Retrieve next posts
		// - Post OQL
		$iDaysOfHistory = MetaModel::GetModuleSetting('molkobain-pixbox', 'post_reader_days_of_history', 30);
		$oPostSearch = DBObjectSearch::FromOQL('SELECT Post WHERE creation_date >= DATE_SUB(NOW(), INTERVAL '.$iDaysOfHistory.' DAY)');
		$oScopeHelper->AddScopeToQuery($oPostSearch, 'Post');
		// - Post medias OQL
		$oMediasSearch = DBObjectSearch::FromOQL('SELECT Attachment WHERE item_class = :obj_class AND item_id = :obj_key');

		$oPostSet = new DBObjectSet($oPostSearch);
		$oPostSet->SetLimit(ReaderBrick::DEFAULT_MESSAGES_PAGINATION);
		$oPostSet->OptimizeColumnLoad(array('id','author_id', 'message', 'creation_date'));

		$aPosts = array();
		while($oPost = $oPostSet->Fetch())
		{
			// Prepare person picture URL
			$sAuthorPictureUrl = $oUrlGenerator->generate(
				'p_object_document_display',
				array(
					'sObjectClass' => 'Person',
					'sObjectId' => $oPost->Get('author_id'),
					'sObjectField' =>'picture'
				),
				UrlGenerator::ABSOLUTE_URL
			);

			// Base information
			$aPost = array(
				'id' => $oPost->GetKey(),
				'author' => array(
					'name' => $oPost->Get('author_id_friendlyname'),
					'picture_url' => $sAuthorPictureUrl,
				),
				'message' => $oPost->Get('message'),
				'date' => $oPost->Get('creation_date'),
				'medias' => array(
					'count' => 0,
					'items' => array(),
				),
			);

			// Medias
			$oMediaSet = new DBObjectSet($oMediasSearch, array(), array('obj_class' => 'Post', 'obj_key' => $oPost->GetKey()));
			$oMediaSet->OptimizeColumnLoad(array('id', 'contents'));
			while($oMedia = $oMediaSet->Fetch())
			{
				/** @var \ormDocument $oMediaORM */
				$oMediaORM = $oMedia->Get('contents');

				$sMediaType = $oMediaORM->GetMainMimeType();
				if(!in_array($sMediaType, ReaderBrick::GetSupportedMediaTypes()))
				{
					continue;
				}

				// Prepare media URL
				$sMediaUrl = $oUrlGenerator->generate(
					'p_object_document_display',
					array(
						'sObjectClass' => get_class($oMedia),
						'sObjectId' => $oMedia->GetKey(),
						'sObjectField' =>'contents'
					),
					UrlGenerator::ABSOLUTE_URL
				);

				$aMedia = array(
					'id' => $oMedia->GetKey(),
					'type' => $sMediaType,
					'mimetype' => $oMediaORM->GetMimeType(),
					'url' => $sMediaUrl,
				);

				$aPost['medias']['items'][$oMedia->GetKey()] = $aMedia;
				$aPost['medias']['count']++;
			}

			// Add to item collection
			$aPosts[$oPost->GetKey()] = $aPost;
		}

		$aData['count'] = count($aPosts);
		$aData['items'] = $aPosts;

		return $oApp->json($aData);
	}
}