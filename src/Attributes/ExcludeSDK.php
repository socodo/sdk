<?php

namespace Socodo\SDK\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
class ExcludeSDK
{
}