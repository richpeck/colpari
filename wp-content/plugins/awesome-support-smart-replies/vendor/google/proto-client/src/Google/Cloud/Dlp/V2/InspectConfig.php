<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/privacy/dlp/v2/dlp.proto

namespace Google\Cloud\Dlp\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Configuration description of the scanning process.
 * When used with redactContent only info_types and min_likelihood are currently
 * used.
 *
 * Generated from protobuf message <code>google.privacy.dlp.v2.InspectConfig</code>
 */
class InspectConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * Restricts what info_types to look for. The values must correspond to
     * InfoType values returned by ListInfoTypes or found in documentation.
     * Empty info_types runs all enabled detectors.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.InfoType info_types = 1;</code>
     */
    private $info_types;
    /**
     * Only returns findings equal or above this threshold. The default is
     * POSSIBLE.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.Likelihood min_likelihood = 2;</code>
     */
    private $min_likelihood = 0;
    /**
     * Generated from protobuf field <code>.google.privacy.dlp.v2.InspectConfig.FindingLimits limits = 3;</code>
     */
    private $limits = null;
    /**
     * When true, a contextual quote from the data that triggered a finding is
     * included in the response; see Finding.quote.
     *
     * Generated from protobuf field <code>bool include_quote = 4;</code>
     */
    private $include_quote = false;
    /**
     * When true, excludes type information of the findings.
     *
     * Generated from protobuf field <code>bool exclude_info_types = 5;</code>
     */
    private $exclude_info_types = false;
    /**
     * Custom infoTypes provided by the user.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.CustomInfoType custom_info_types = 6;</code>
     */
    private $custom_info_types;
    /**
     * List of options defining data content to scan.
     * If empty, text, images, and other content will be included.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.ContentOption content_options = 8;</code>
     */
    private $content_options;

    public function __construct() {
        \GPBMetadata\Google\Privacy\Dlp\V2\Dlp::initOnce();
        parent::__construct();
    }

    /**
     * Restricts what info_types to look for. The values must correspond to
     * InfoType values returned by ListInfoTypes or found in documentation.
     * Empty info_types runs all enabled detectors.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.InfoType info_types = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInfoTypes()
    {
        return $this->info_types;
    }

    /**
     * Restricts what info_types to look for. The values must correspond to
     * InfoType values returned by ListInfoTypes or found in documentation.
     * Empty info_types runs all enabled detectors.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.InfoType info_types = 1;</code>
     * @param \Google\Cloud\Dlp\V2\InfoType[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInfoTypes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dlp\V2\InfoType::class);
        $this->info_types = $arr;

        return $this;
    }

    /**
     * Only returns findings equal or above this threshold. The default is
     * POSSIBLE.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.Likelihood min_likelihood = 2;</code>
     * @return int
     */
    public function getMinLikelihood()
    {
        return $this->min_likelihood;
    }

    /**
     * Only returns findings equal or above this threshold. The default is
     * POSSIBLE.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.Likelihood min_likelihood = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setMinLikelihood($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Dlp\V2\Likelihood::class);
        $this->min_likelihood = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.google.privacy.dlp.v2.InspectConfig.FindingLimits limits = 3;</code>
     * @return \Google\Cloud\Dlp\V2\InspectConfig_FindingLimits
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * Generated from protobuf field <code>.google.privacy.dlp.v2.InspectConfig.FindingLimits limits = 3;</code>
     * @param \Google\Cloud\Dlp\V2\InspectConfig_FindingLimits $var
     * @return $this
     */
    public function setLimits($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dlp\V2\InspectConfig_FindingLimits::class);
        $this->limits = $var;

        return $this;
    }

    /**
     * When true, a contextual quote from the data that triggered a finding is
     * included in the response; see Finding.quote.
     *
     * Generated from protobuf field <code>bool include_quote = 4;</code>
     * @return bool
     */
    public function getIncludeQuote()
    {
        return $this->include_quote;
    }

    /**
     * When true, a contextual quote from the data that triggered a finding is
     * included in the response; see Finding.quote.
     *
     * Generated from protobuf field <code>bool include_quote = 4;</code>
     * @param bool $var
     * @return $this
     */
    public function setIncludeQuote($var)
    {
        GPBUtil::checkBool($var);
        $this->include_quote = $var;

        return $this;
    }

    /**
     * When true, excludes type information of the findings.
     *
     * Generated from protobuf field <code>bool exclude_info_types = 5;</code>
     * @return bool
     */
    public function getExcludeInfoTypes()
    {
        return $this->exclude_info_types;
    }

    /**
     * When true, excludes type information of the findings.
     *
     * Generated from protobuf field <code>bool exclude_info_types = 5;</code>
     * @param bool $var
     * @return $this
     */
    public function setExcludeInfoTypes($var)
    {
        GPBUtil::checkBool($var);
        $this->exclude_info_types = $var;

        return $this;
    }

    /**
     * Custom infoTypes provided by the user.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.CustomInfoType custom_info_types = 6;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getCustomInfoTypes()
    {
        return $this->custom_info_types;
    }

    /**
     * Custom infoTypes provided by the user.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.CustomInfoType custom_info_types = 6;</code>
     * @param \Google\Cloud\Dlp\V2\CustomInfoType[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setCustomInfoTypes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dlp\V2\CustomInfoType::class);
        $this->custom_info_types = $arr;

        return $this;
    }

    /**
     * List of options defining data content to scan.
     * If empty, text, images, and other content will be included.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.ContentOption content_options = 8;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getContentOptions()
    {
        return $this->content_options;
    }

    /**
     * List of options defining data content to scan.
     * If empty, text, images, and other content will be included.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.ContentOption content_options = 8;</code>
     * @param int[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setContentOptions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::ENUM, \Google\Cloud\Dlp\V2\ContentOption::class);
        $this->content_options = $arr;

        return $this;
    }

}

