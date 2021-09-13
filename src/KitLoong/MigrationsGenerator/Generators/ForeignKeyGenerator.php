<?php namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\MigrationMethod\Foreign;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class ForeignKeyGenerator
{
    public function generate(Table $table, ForeignKeyConstraint $foreignKey): ColumnMethod
    {
        if ($this->shouldSkipName($table->getName(), $foreignKey)) {
            $method = new ColumnMethod(Foreign::FOREIGN, $foreignKey->getUnquotedLocalColumns());
        } else {
            $method = new ColumnMethod(Foreign::FOREIGN, $foreignKey->getUnquotedLocalColumns(), $foreignKey->getName());
        }

        $method->chain(Foreign::REFERENCES, $foreignKey->getUnquotedForeignColumns())
            ->chain(Foreign::ON, $foreignKey->getForeignTableName());

        if ($foreignKey->hasOption('onUpdate')) {
            $method->chain(Foreign::ON_UPDATE, $foreignKey->getOption('onUpdate'));
        }

        if ($foreignKey->hasOption('onDelete')) {
            $method->chain(Foreign::ON_DELETE, $foreignKey->getOption('onDelete'));
        }

        return $method;
    }

    public function generateDrop(ForeignKeyConstraint $foreignKey): ColumnMethod
    {
        return new ColumnMethod(Foreign::FOREIGN, $foreignKey->getName());
    }

    private function shouldSkipName(string $table, ForeignKeyConstraint $foreignKey): bool
    {
        if (app(MigrationsGeneratorSetting::class)->isIgnoreForeignKeyNames()) {
            return true;
        }

        $guessIndexName = strtolower($table.'_'.implode('_', $foreignKey->getUnquotedLocalColumns()).'_foreign');
        $guessIndexName = str_replace(['-', '.'], '_', $guessIndexName);
        return $guessIndexName === $foreignKey->getName();
    }
}
