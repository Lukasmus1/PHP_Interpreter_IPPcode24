<?php

namespace IPP\Student;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\enums\FramesEnum;
use DOMNodeList;
use DOMElement;

//Pomocná třída pro různé věco
class Tools
{
    /**
     * Pomocná třída pro získání rámce a jména proměnné
     * @param DOMNodeList<DOMElement> $child
     * @return array{0: FramesEnum, 1: string}
     */
    public static function GetFrameAndName(DOMNodeList $child): array
    {
        //Tato kontrola je redundantní ale phpstan jinak řve
        if ($child->length > 0 && $child->item(0) !== null)
        {
            $rawFrame = $child->item(0)->nodeValue;
            if ($rawFrame == null)
            {
                return [FramesEnum::ERR, "err"];
            }
        } else
        {
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

    /**
     * Pomocná třída pro získání datového typu a hodnoty proměnné
     * @param DOMNodeList<DOMElement> $child
     * @return array{0: DataTypeEnum, 1: string|null}
     */
    public static function GetTypeAndValue(DOMNodeList $child): array
    {
        $node = $child->item(0);
        $type = "phpstan je supr";
        if ($node == null)
        {
            return [DataTypeEnum::ERR, "err"];
        }

        if ($node instanceof DOMElement)
        {
            $type = $node->getAttribute("type");
        }

        return match ($type)
        {
            "string" => [DataTypeEnum::STRING, $node->nodeValue],
            "int" => [DataTypeEnum::INT, $node->nodeValue],
            "bool" => [DataTypeEnum::BOOL, $node->nodeValue],
            "nil" => [DataTypeEnum::NIL, $node->nodeValue],
            "var" => [DataTypeEnum::VAR, "var"],
            default => [DataTypeEnum::ERR, "err"],
        };
    }

    /**
     * Pomocná třída pro nalezení proměnné v nějakém rámci
     * @param VarClass $sym
     * @return VarClass|int
     */
    public static function FindInFrame(VarClass $sym): VarClass|int
    {
        switch ($sym->Frame)
        {
            case FramesEnum::G:
                foreach (Frames::$GlobalFrame as $item)
                {
                    if ($item->Name == $sym->Name)
                    {
                        return $item;
                    }
                }
                return 54;

            case FramesEnum::T:
                if (count(TempFrame::$TempFrame) == 0)
                {
                    return 52;
                }
                foreach (TempFrame::$TempFrame as $item)
                {
                    if ($item == null)
                    {
                        continue;
                    }
                    if ($item->Name == $sym->Name)
                    {
                        return $item;
                    }
                }
                return 54;

            case FramesEnum::L:
                if (!Frames::$localInitialized)
                {
                    return 52;
                }

                $idk = Frames::$LocalFrame[0];
                foreach ($idk->TempFrame as $item)
                {
                    if ($item == null)
                    {
                        continue;
                    }
                    if ($item->Name == $sym->Name)
                    {
                        return $item;
                    }
                }
                return 54;

            default:
                return 54;
        }
    }

    /**
     * Pomocná třída pro vložení proměnné do daného rámce
     * @param VarClass $var
     * @return int
     */
    public static function PushToFrame(VarClass $var): int
    {
        switch ($var->Frame)
        {
            case FramesEnum::G:
                foreach (Frames::$GlobalFrame as $item) {
                    if ($item->Name == $var->Name) {
                        return 52;
                    }
                }
                Frames::$GlobalFrame[] = $var;
                return 0;

            case FramesEnum::L:
                if (!Frames::$localInitialized)
                {
                    return 55;
                }

                foreach (Frames::$LocalFrame[0]->TempFrame as $item)
                {
                    if ($item == null)
                    {
                        continue;
                    }
                    if ($item->Name == $var->Name)
                    {
                        return 52;
                    }
                }
                Frames::$LocalFrame[0]->TempFrame[] = $var;
                return 0;

            case FramesEnum::T:
                $isInitialized = false;
                foreach (TempFrame::$TempFrame as $item)
                {
                    if ($item == null)
                    {
                        $isInitialized = true;
                        continue;
                    }
                    if ($item->Name == $var->Name) {
                        return 52;
                    }
                }

                if (!$isInitialized)
                {
                    return 55;
                }

                TempFrame::$TempFrame[] = $var;
                return 0;

            default:
                return 52;
        }
    }
}