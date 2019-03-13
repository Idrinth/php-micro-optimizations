<?php

use De\Idrinth\PHPMicroOptimizations\ConditionSimplifier;
use De\Idrinth\PHPMicroOptimizations\DefinitionCollector;
use De\Idrinth\PHPMicroOptimizations\ExpressionEvaluator;
use De\Idrinth\PHPMicroOptimizations\FullyQualifier;
use De\Idrinth\PHPMicroOptimizations\FunctionToConstant;
use De\Idrinth\PHPMicroOptimizations\UseRemover;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\Finder;

require_once 'vendor/autoload.php';

$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$traverser = new NodeTraverser();
$traverser->addVisitor($visitor = new DefinitionCollector());
$asts = [];
foreach(Finder::create()->files()->name('*.php')->in(__DIR__.'/test') as $file) {
    $visitor->resetNamespace();
    $ast = $parser->parse(file_get_contents("$file"));
    $traverser->traverse($ast);
    $asts["$file"] = $ast;
}
$traverser->removeVisitor($visitor);
$traverser->addVisitor(new NameResolver());
$traverser->addVisitor($fq = new FullyQualifier($visitor->getFunctions(), $visitor->getConstants()));
$traverser->addVisitor(new UseRemover());
$traverser->addVisitor($ev = new ExpressionEvaluator());
$traverser->addVisitor($cs = new ConditionSimplifier());
$traverser->addVisitor($f2c = new FunctionToConstant());
$traverser->addVisitor($ev);
$traverser->addVisitor($cs);
$writer = new Standard();
foreach ($asts as $file => $ast) {
    $file = str_replace('test', 'tests', $file);
    $fq->resetNamespace();
    $f2c->setFile($file);
    $traverser->traverse($ast);
    file_put_contents($file, $writer->prettyPrintFile($ast));
}