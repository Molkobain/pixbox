<?php

namespace Molkobain\iTop\Extension\Pixbox\Extension;

use DBObjectSearch;
use DBObjectSet;
use iApplicationObjectExtension;
use MetaModel;
use ormDocument;

class ApplicationObjectExtension implements iApplicationObjectExtension
{

	/**
	 * @inheritDoc
	 */
	public function OnIsModified($oObject)
	{
		return;
	}

	/**
	 * @inheritDoc
	 */
	public function OnCheckToWrite($oObject)
	{
		return;
	}

	/**
	 * @inheritDoc
	 */
	public function OnCheckToDelete($oObject)
	{
		return;
	}

	/**
	 * @inheritDoc
	 * @throws \OQLException
	 * @throws \CoreUnexpectedValue
	 * @throws \CoreException
	 */
	public function OnDBUpdate($oObject, $oChange = null)
	{
		$this->ResizeAttachmentsForPost($oObject);

		return;
	}

	/**
	 * @inheritDoc
	 * @throws \OQLException
	 * @throws \CoreUnexpectedValue
	 * @throws \CoreException
	 */
	public function OnDBInsert($oObject, $oChange = null)
	{
		$this->ResizeAttachmentsForPost($oObject);

		return;
	}

	/**
	 * @inheritDoc
	 */
	public function OnDBDelete($oObject, $oChange = null)
	{
		return;
	}

	/**
	 * @param \DBObject $oObject
	 *
	 * @return \ormDocument
	 * @throws \OQLException
	 * @throws \CoreUnexpectedValue
	 * @throws \CoreException
	 */
	protected function ResizeAttachmentsForPost($oObject)
	{
		// Attachment processing
		if (is_a($oObject, 'Post'))
		{
			// Resize Post attachments
			$oSearch = DBObjectSearch::FromOQL('SELECT Attachment WHERE item_class = :obj_class AND item_id = :obj_key');
			$oSet = new DBObjectSet($oSearch, array(), array('obj_class' => get_class($oObject), 'obj_key' => $oObject->GetKey()));
			while($oAttachment = $oSet->Fetch())
			{
				// Note: This is mostly inspired by InlineImage::ResizeImageToFit() 🤞
				/** @var \ormDocument $oImage */
				$oImage = $oAttachment->Get('contents');

				$img = false;
				switch ($oImage->GetMimeType())
				{
					case 'image/gif':
					case 'image/jpeg':
					case 'image/png':
						$img = @imagecreatefromstring($oImage->GetData());
						break;

					default:
						// Unsupported image type, return the image as-is
						$img = false;
						break;
				}

				if ($img !== false)
				{
					// Let's scale the image, preserving the transparency for GIFs and PNGs
					$iWidth = imagesx($img);
					$iHeight = imagesy($img);
					$iMaxImageSize = (int)MetaModel::GetConfig()->GetModuleSetting('molkobain-pixbox', 'picture_max_width', 1920);

					if (($iMaxImageSize > 0) && ($iWidth <= $iMaxImageSize) && ($iHeight <= $iMaxImageSize))
					{
						// No need to resize
						continue;
					}

					$fScale = min($iMaxImageSize / $iWidth, $iMaxImageSize / $iHeight);

					$iNewWidth = $iWidth * $fScale;
					$iNewHeight = $iHeight * $fScale;

					$new = imagecreatetruecolor($iNewWidth, $iNewHeight);

					// Preserve transparency
					if (($oImage->GetMimeType() == "image/gif") || ($oImage->GetMimeType() == "image/png"))
					{
						imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
						imagealphablending($new, false);
						imagesavealpha($new, true);
					}

					imagecopyresampled($new, $img, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $iWidth, $iHeight);

					ob_start();
					switch ($oImage->GetMimeType())
					{
						case 'image/gif':
							imagegif($new); // send image to output buffer
							break;

						case 'image/jpeg':
							imagejpeg($new, null, 80); // null = send image to output buffer, 80 = good quality
							break;

						case 'image/png':
							imagepng($new, null, 5); // null = send image to output buffer, 5 = medium compression
							break;
					}
					$oNewImage = new ormDocument(ob_get_contents(), $oImage->GetMimeType(), $oImage->GetFileName());
					@ob_end_clean();

					imagedestroy($img);
					imagedestroy($new);

					$oAttachment->Set('contents', $oNewImage);
					$oAttachment->DBUpdate();
				}
			}
		}
	}
}