<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Latte;

use Latte;

/**
 * ============================== Macro "img" ==============================
 *
 * @syntax:
 *      base:       {img $info, ?$modifier, ?$generatorName}
 *      n-macro:    n:img="$info, ?$modifier, ?$generatorName"
 *
 * @arguments:
 *      $info:
 *          required: YES
 *          type: string or \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo or \SixtyEightPublishers\ImageStorage\ImageInfo
 *      $modifier:
 *          required: NO
 *          type: NULL or string (preset alias) or array
 *
 *          If $modifier is not set, `original` image path will be returned
 *      $imageStorageName:
 *          required: NO
 *          type: NULL or string
 *
 *          If $generatorName is not set and $info is not instance of \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo, `default` storage will be used
 * @examples:
 *      1) <img n:img="'NAMESPACE/FILE.jpeg'" alt="...">      => returns `original` image path
 *      2) <img n:img="'NAMESPACE/FILE.jpeg', [ w => 300, h => 300 ]" alt="...">      => returns 300x300 image path
 *      2) <img n:img="'NAMESPACE/FILE.jpeg', MyPreset" alt="...">      => returns image path modified with preset `MyPreset`
 *      3) <img n:img="$info, $modifier" alt="...">   => returns path of $info (string|info object) modified by $modifier (NULL|array|string) rules
 *      3) <img n:img="'NAMESPACE/FILE.jpeg', NULL, 's3'" alt="...">   => returns `original` image path from storage with name `s3`
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
 *      $modifier: @see "img" macro
 *      $imageStorageName:  @see "img" macro
 *
 * @examples:
 *      1) <img n:srcset="'TEST/TESTID/img.jpeg' w_descriptor(200, 400, 600)" alt="...">    => returns `original` image path
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
		$me->addMacro('img', [$me, 'beginImage'], NULL, [$me, 'attrImage']);
		$me->addMacro('srcset', [$me, 'beginSrcSet'], NULL, [$me, 'attrSrcSet']);
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 */
	public function beginImage(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		return $writer->write('echo %escape($this->global->imageStorageLatteFacade->link(%node.args));');
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 */
	public function attrImage(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		return $writer->write(
			'echo " " . %word . "\""; %raw echo "\"";',
			$node->htmlNode->name === 'a' ? 'href=' : 'src=',
			$this->beginImage($node, $writer)
		);
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 */
	public function beginSrcSet(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		return $writer->write('echo %escape($this->global->imageStorageLatteFacade->srcSet(%node.args));');
	}

	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 *
	 * @return string
	 */
	public function attrSrcSet(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		$srcset = $writer->write(
			'echo " srcset=\""; %raw echo "\""; ',
			$this->beginSrcSet($node, $writer)
		);

		$tokens = $node->tokenizer;
		$newArgs = '';
		$currentArgNumber = 1;

		$tokens->reset();

		while ($tokens->nextToken()) {
			if (0 === $tokens->depth && $tokens->isCurrent(',')) {
				$currentArgNumber++;
			}

			# join everything except second argument
			if (2 !== $currentArgNumber) {
				$newArgs .= $tokens->currentValue();
			}
		}

		$node->setArgs($newArgs);
		$writer = Latte\PhpWriter::using($node);

		return $srcset . $writer->write(
			'echo "src=\""; %raw echo "\"";',
			$this->beginImage($node, $writer)
			);
	}
}
