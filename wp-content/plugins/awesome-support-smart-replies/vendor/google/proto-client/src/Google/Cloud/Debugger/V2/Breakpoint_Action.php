<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/devtools/clouddebugger/v2/data.proto

namespace Google\Cloud\Debugger\V2;

/**
 * Actions that can be taken when a breakpoint hits.
 * Agents should reject breakpoints with unsupported or unknown action values.
 *
 * Protobuf enum <code>Google\Devtools\Clouddebugger\V2\Breakpoint\Action</code>
 */
class Breakpoint_Action
{
    /**
     * Capture stack frame and variables and update the breakpoint.
     * The data is only captured once. After that the breakpoint is set
     * in a final state.
     *
     * Generated from protobuf enum <code>CAPTURE = 0;</code>
     */
    const CAPTURE = 0;
    /**
     * Log each breakpoint hit. The breakpoint remains active until
     * deleted or expired.
     *
     * Generated from protobuf enum <code>LOG = 1;</code>
     */
    const LOG = 1;
}

