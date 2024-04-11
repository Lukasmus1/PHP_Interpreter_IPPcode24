<?php

namespace IPP\Student;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\enums\FramesEnum;

class Tools
{
    /**
     * @return array{0: FramesEnum, 1: string}
     */
    public static function GetFrameAndName($child): array
    {
        //Toto bude existovat vždycky ale compiler o tom neví
        if ($child->length > 0 && $child->item(0) !== null) {
            $rawFrame = $child->item(0)->nodeValue;
            if ($rawFrame == null) {
                return [FramesEnum::ERR, "err"];
            }
        } else {
            //Nikdy by se to tady dostat nemělo
            return [FramesEnum::ERR, "err"];
        }

        $explode = explode('@', $rawFrame);
        return match ($explode[0]) {
            "GF" => [FramesEnum::G, implode('@', array_slice($explode, 1))],
            "LF" => [FramesEnum::L, implode('@', array_slice($explode, 1))],
            "TF" => [FramesEnum::T, implode('@', array_slice($explode, 1))],
            default => [FramesEnum::G, "err"],
        };
    }

    public static function GetTypeAndValue($child): array
    {
        $type = $child->item(0)->getAttribute("type");

        return match ($type) {
            "string" => [DataTypeEnum::STRING, $child->item(0)->nodeValue],
            "int" => [DataTypeEnum::INT, $child->item(0)->nodeValue],
            "bool" => [DataTypeEnum::BOOL, $child->item(0)->nodeValue],
            "nil" => [DataTypeEnum::NIL, $child->item(0)->nodeValue],
            "var" => [DataTypeEnum::VAR, "var"],
            default => [DataTypeEnum::ERR, "err"],
        };
    }

    //TODO - UDĚLAT TO PRO VŠECHNY TYPY FRAME
    public static function FindInFrame(string $sym): VarClass|null
    {
        foreach (Frames::$GlobalFrame as $item) {
            if ($item->Name == $sym) {
                return $item;
            }
        }
        return null;
    }

    //TODO - UDĚLAT TO PRO VŠECHNY TYPY FRAME
    public static function PushToFrame(VarClass $var): bool
    {
        foreach (Frames::$GlobalFrame as $item) {
            if ($item->Name == $var->Name) {
                return false;
            }
        }

        Frames::$GlobalFrame[] = $var;
        return true;
    }

    /**public static function TypeChecker(DataTypeEnum $type, mixed $value): bool
    {
        if (is_numeric($value))
        {
            return $type === DataTypeEnum::INT;
        }
        else if ($value == "true" || $value == "false")
        {
            return $type === DataTypeEnum::BOOL;
        }

        return match (gettype($value)) {
            "string" => $type === DataTypeEnum::STRING,
            "NULL" => $type === DataTypeEnum::NIL,
            default => false,
        };
    }**/
}