<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Utils;

trait HasNotFilledPlaceholdersTrait
{
    /**
     * @param string[] $placeholders
     */
    protected function hasNotFilledPlaceholders(array $placeholders, string $subject): bool
    {
        static $subjects = [];

        if (!in_array($subject, $subjects, true)) {
            $subjects[] = (object) ['subject' => $subject, 'counter' => 1];
        } else {
            $index = array_search($subject, $subjects, true);
            ++$subjects[$index]->counter;

            if ($subjects[$index]->counter > 100) {
                throw new \LogicException(sprintf("Infinity loop detected! Expression has too many iterations.\nSubject:\n\n\%s", $subject));
            }
        }

        foreach ($placeholders as $placeholder) {
            if (false !== strpos($subject, $placeholder)) {
                return true;
            }
        }

        return false;
    }
}
