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
 *      base:       {srcset $info, ?$modifier, ?$generatorName}
 *      n-macro:    n:srcset="$info, ?$modifier, ?$generatorName"   => this usage generates `n:img` also
 *
 * @arguments
 *      Argument are same as for macro "img".
 *      Descriptors (pixel-densities with device-pixel-ratios) are defined in Extension's config (key `descriptors`)
 *
 * @examples:
 *      1) <img n:srcset="'TEST/TESTID/img.jpeg'" alt="...">    => returns `original` image path
 *      1) <img n:srcset="'TEST/TESTID/img.jpeg', [ w: 300 ], 's3'" alt="...">     => returns image path with 300px width from `s3` storage
 *
 */
final class ImageStorageMacroSet extends Latte\Macros\MacroSet
{
	/**
	 * @param \Latte\Compiler $compiler
	 */
	public static function install(Latte\Compiler $compiler)
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
		return $writer->write(
			'echo " srcset=\""; %raw echo "\" src=\""; %raw echo "\""',
			$this->beginSrcSet($node, $writer),
			$this->beginImage($node, $writer)
		);
	}
}
