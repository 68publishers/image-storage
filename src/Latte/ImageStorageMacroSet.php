<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Latte;

use Latte;

/**
 * ============================== Macro "img" ==============================
 *
 * @syntax:
 *      base:       {img $info, $modifier, ?$generatorName}
 *      n-macro:    n:img="$info, $modifier, ?$generatorName"
 *
 * @arguments:
 *      $info:
 *          required: YES
 *          type: string or \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo or \SixtyEightPublishers\ImageStorage\ImageInfo
 *      $modifier:
 *          required: YES
 *          type: string (preset alias) or array
 *      $imageStorageName:
 *          required: NO
 *          type: NULL or string
 *
 *          If $generatorName is not set and $info is not instance of \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo, `default` storage will be used
 * @examples:
 *      1) <img n:img="'NAMESPACE/FILE.jpeg', [original => true]" alt="...">      => returns `original` image path
 *      2) <img n:img="'NAMESPACE/FILE.jpeg', [ w => 300, h => 300 ]" alt="...">      => returns 300x300 image path
 *      2) <img n:img="'NAMESPACE/FILE.jpeg', MyPreset" alt="...">      => returns image path modified with preset `MyPreset`
 *      3) <img n:img="$info, $modifier" alt="...">   => returns path of $info (string|info object) modified by $modifier (array|string) rules
 *      3) <img n:img="'NAMESPACE/FILE.jpeg', [original => true], 's3'" alt="...">   => returns `original` image path from storage with name `s3`
 *
 * ============================== Macro "srcset" ==============================
 *
 * @syntax:
 *      base:       {srcset $info, $descriptor, ?$modifier, ?$generatorName}
 *      n-macro:    n:srcset="$info, $descriptor, ?$modifier, ?$generatorName"   => this usage generates `n:img` also
 *
 * @arguments
 *      $info: @see "img" macro
 *      $descriptor:
 *          required: YES
 *          type: SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor
 *
 *          You can use "factory" functions w_descriptor(...) and x_descriptor(...) for comfortable manipulations
 *      $modifier:
 *          required: NO
 *          type: null or string (preset alias) or array
 *      $imageStorageName:  @see "img" macro
 *
 * @examples:
 *      1) <img n:srcset="'TEST/TESTID/img.jpeg' w_descriptor(200, 400, 600)" alt="...">    => returns paths with widths 200px, 400px and 600px
 *      1) <img n:srcset="'TEST/TESTID/img.jpeg', x_descriptor(1, 2), [ w: 300 ], 's3'" alt="...">     => returns image path with 300px width from `s3` storage
 *
 */
final class ImageStorageMacroSet extends Latte\Macros\MacroSet
{
	/**
	 * @param \Latte\Compiler $compiler
	 *
	 * @return void
	 */
	public static function install(Latte\Compiler $compiler): void
	{
		$me = new static($compiler);

		$me->addMacro('img', [$me, 'beginSrc'], NULL, [$me, 'attrSrc']); # @todo for back compatibility only, n:img and {img} will be removed in next major release
		$me->addMacro('src', [$me, 'beginSrc'], NULL, [$me, 'attrSrc']);
		$me->addMacro('srcset', [$me, 'beginSrcSet'], NULL, [$me, 'attrSrcSet']);
		$me->addMacro('picture', NULL, NULL, [$me, 'attrPicture']);
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 * @throws \Latte\CompileException
	 */
	public function beginSrc(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		return $this->resolveBaseWriter($node, $writer)->write('echo %escape($this->global->imageStorageLatteFacade->getSrcAttributes(NULL, %node.args)["src"]);');
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 * @throws \Latte\CompileException
	 */
	public function beginSrcSet(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		return $this->resolveBaseWriter($node, $writer)->write('echo %escape($this->global->imageStorageLatteFacade->getSrcSetAttributes(NULL, %node.args)["srcset");');
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 * @throws \Latte\CompileException
	 */
	public function attrSrc(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		$writer = $this->resolveBaseWriter($node, $writer);
		$type = 'NULL';

		if (isset($node->htmlNode->attrs['type']) && $this->isSourceMacroNode($node)) {
			$type = $node->htmlNode->attrs['type'];
		}

		$output = $writer->write(
			'$__tmp_image_attributes = $this->global->imageStorageLatteFacade->getSrcAttributes(%word, %node.args); '
			. 'echo " " . %word . "\"" . %escape($__tmp_image_attributes["src"]) . "\""; ',
			$type,
			$node->htmlNode->name === 'a' ? 'href=' : 'src='
		);

		if (!isset($node->htmlNode->attrs['type']) && $this->isSourceMacroNode($node)) {
			$output .= $writer->write('echo " type=\"" . %escape($__tmp_image_attributes["type"]) . "\"";');
		}

		return $output;
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 * @throws \Latte\CompileException
	 */
	public function attrSrcSet(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		$writer = $this->resolveBaseWriter($node, $writer);
		$type = 'NULL';

		if (isset($node->htmlNode->attrs['type']) && $this->isSourceMacroNode($node)) {
			$type = $node->htmlNode->attrs['type'];
		}

		$output = $writer->write(
			'$__tmp_image_attributes = $this->global->imageStorageLatteFacade->getSrcSetAttributes(%word, %node.args); '
			. 'echo " srcset=\"" . %escape($__tmp_image_attributes["srcset"]) . "\""; ',
			$type
		);

		if (!isset($node->htmlNode->attrs['type']) && $this->isSourceMacroNode($node)) {
			$output .= $writer->write('echo " type=\"" . %escape($__tmp_image_attributes["type"]) . "\""; ');
		}

		if ('img' !== $node->htmlNode->name || isset($node->htmlNode->attrs['src']) || isset($node->htmlNode->macroAttrs['img']) || isset($node->htmlNode->macroAttrs['src'])) {
			return $output;
		}

		return $output . $writer->write('echo "src=\"" . %escape($__tmp_image_attributes["src"]) . "\"";');
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 */
	public function attrPicture(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		$node->empty = FALSE;

		return '';
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return \Latte\PhpWriter
	 * @throws \Latte\CompileException
	 */
	private function resolveBaseWriter(Latte\MacroNode $node, Latte\PhpWriter $writer): Latte\PhpWriter
	{
		if ($this->isSourceMacroNode($node)) {
			$this->validateSourceMacroNode($node);
		}

		if ($this->isMacroNodeInPicture($node)) {
			$writer = $this->createComposedWritter($node->parentNode, $node);
		}

		return $writer;
	}

	/**
	 * @param \Latte\MacroNode $node
	 *
	 * @return bool
	 */
	private function isSourceMacroNode(Latte\MacroNode $node): bool
	{
		return 'source' === $node->htmlNode->name;
	}

	/**
	 * @param \Latte\MacroNode $node
	 *
	 * @return bool
	 */
	private function isMacroNodeInPicture(Latte\MacroNode $node): bool
	{
		return NULL !== $node->htmlNode->parentNode && 'picture' === $node->htmlNode->parentNode->name && isset($node->htmlNode->parentNode->macroAttrs['picture']);
	}

	/**
	 * @param \Latte\MacroNode $node
	 *
	 * @return void
	 * @throws \Latte\CompileException
	 */
	private function validateSourceMacroNode(Latte\MacroNode $node): void
	{
		# if the macro is applied on a SOURCE element than parent element must be the PICTURE element with an attribute n:picture
		if ($this->isSourceMacroNode($node) && !$this->isMacroNodeInPicture($node)) {
			throw new Latte\CompileException('The parent element must be <picture> with an attirbute n:picture.');
		}
	}

	/**
	 * @param \Latte\MacroNode $pictureNode
	 * @param \Latte\MacroNode $imagetNode
	 *
	 * @return \Latte\PhpWriter
	 */
	private function createComposedWritter(Latte\MacroNode $pictureNode, Latte\MacroNode $imagetNode): Latte\PhpWriter
	{
		$pictureTokens = $pictureNode->tokenizer;
		$imageTokens = $imagetNode->tokenizer;
		$numberOfRemainingImageArguments = 'srcset' === $imagetNode->name ? 2 : 1;
		$newArgs = '';

		$pictureTokens->reset();
		$imageTokens->reset();

		# append an image info only
		while ($pictureTokens->nextToken()) {
			if (0 === $pictureTokens->depth && $pictureTokens->isCurrent(',')) {
				break;
			}

			$newArgs .= $pictureTokens->currentValue();
		}

		$newArgs .= ', ';

		# append another arguments. src = $modifier | srcset = $descriptor, ?$modifier
		while ($imageTokens->nextToken()) {
			if ((0 === $imageTokens->depth && $imageTokens->isCurrent(',')) || !$imageTokens->isNext()) {
				$numberOfRemainingImageArguments--;
			}

			$newArgs .= $imageTokens->currentValue();
		}

		if ($pictureTokens->isNext()) {
			# fill remaining values with null
			if (0 < $numberOfRemainingImageArguments) {
				$newArgs .= ', ' . implode(', ', array_fill(0, $numberOfRemainingImageArguments, 'NULL'));
			}

			# append the last argument - ?$storageName
			$newArgs .= ',' . $pictureTokens->joinAll();
		}

		$imagetNode->setArgs($newArgs);

		$pictureTokens->reset();
		$imageTokens->reset();

		return Latte\PhpWriter::using($imagetNode);
	}
}
