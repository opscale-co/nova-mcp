<?php

namespace Workbench\App\Resolvers;

use Opscale\NovaMCP\Contracts\ProcessResolver;

/**
 * Workbench Process Resolver
 *
 * Example implementation of ProcessResolver for the workbench environment.
 * This provides BPMN 2.0 process definitions for user management workflows.
 */
class WorkbenchProcessResolver implements ProcessResolver
{
    /**
     * Resolve and return the complete BPMN 2.0 XML representation of business processes.
     *
     * This example defines simple user management processes.
     * In a real implementation, this would define all your business workflows
     * with comprehensive documentation.
     */
    public function resolve(): string
    {
        return <<<'BPMN'
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL"
             xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI"
             xmlns:dc="http://www.omg.org/spec/DD/20100524/DC"
             xmlns:di="http://www.omg.org/spec/DD/20100524/DI"
             id="Definitions_workbench"
             targetNamespace="http://workbench.nova-mcp.com/bpmn"
             exporter="Nova MCP Workbench"
             exporterVersion="1.0">

  <!-- User Login Process -->
  <process id="user_login" name="User Login" isExecutable="true">
    <documentation>
      Complete workflow for user login with automatic registration.
      If the user is not registered, creates the account, resets password, and sends welcome email.
      If the user is registered, continues with normal login flow.
    </documentation>

    <startEvent id="start_login" name="User Wants to Login">
      <documentation>User attempts to login to the platform</documentation>
    </startEvent>

    <scriptTask id="check_user_exists" name="Check if User is Registered">
      <documentation>
        Query the users table to check if a user with the provided email exists.
        CRUD operation: Read on users table
      </documentation>
    </scriptTask>

    <exclusiveGateway id="gateway_user_registered" name="User Registered?">
      <documentation>Decision point: Does the user already exist in the system?</documentation>
    </exclusiveGateway>

    <!-- Path for NEW users (not registered) -->
    <scriptTask id="create_user" name="Create User">
      <documentation>
        Create a new user record in the database with:
        - Email address
        - Name (if provided)
        - Temporary or null password
        CRUD operation: Create on users table
      </documentation>
    </scriptTask>

    <serviceTask id="reset_password" name="Reset Password">
      <documentation>
        Generate a new password for the user and update their record.
        Logic tool: reset-password action
      </documentation>
    </serviceTask>

    <serviceTask id="send_welcome_email" name="Send Welcome Email">
      <documentation>
        Send a welcome email to the new user with:
        - Welcome message
        - Login credentials
        - Getting started information
        Logic tool: send-welcome-email action
      </documentation>
    </serviceTask>

    <!-- Path for EXISTING users (registered) -->
    <scriptTask id="validate_credentials" name="Validate Credentials">
      <documentation>
        Verify the user's password matches the stored hash.
        CRUD operation: Read on users table
      </documentation>
    </scriptTask>

    <exclusiveGateway id="gateway_credentials_valid" name="Credentials Valid?">
      <documentation>Decision point: Are the provided credentials correct?</documentation>
    </exclusiveGateway>

    <scriptTask id="create_session" name="Create Session">
      <documentation>
        Create a new session for the authenticated user.
        CRUD operation: Create on sessions table
      </documentation>
    </scriptTask>

    <endEvent id="end_login_success" name="Login Successful">
      <documentation>User has been successfully authenticated and session created</documentation>
    </endEvent>

    <endEvent id="end_login_failed" name="Login Failed">
      <documentation>Login failed due to invalid credentials</documentation>
    </endEvent>

    <!-- Sequence Flows -->
    <sequenceFlow id="flow_start" sourceRef="start_login" targetRef="check_user_exists" />
    <sequenceFlow id="flow_to_gateway" sourceRef="check_user_exists" targetRef="gateway_user_registered" />

    <!-- New user path -->
    <sequenceFlow id="flow_not_registered" name="Not Registered" sourceRef="gateway_user_registered" targetRef="create_user" />
    <sequenceFlow id="flow_to_reset" sourceRef="create_user" targetRef="reset_password" />
    <sequenceFlow id="flow_to_welcome" sourceRef="reset_password" targetRef="send_welcome_email" />
    <sequenceFlow id="flow_new_user_success" sourceRef="send_welcome_email" targetRef="end_login_success" />

    <!-- Existing user path -->
    <sequenceFlow id="flow_registered" name="Registered" sourceRef="gateway_user_registered" targetRef="validate_credentials" />
    <sequenceFlow id="flow_to_cred_gateway" sourceRef="validate_credentials" targetRef="gateway_credentials_valid" />
    <sequenceFlow id="flow_cred_valid" name="Valid" sourceRef="gateway_credentials_valid" targetRef="create_session" />
    <sequenceFlow id="flow_cred_invalid" name="Invalid" sourceRef="gateway_credentials_valid" targetRef="end_login_failed" />
    <sequenceFlow id="flow_session_success" sourceRef="create_session" targetRef="end_login_success" />
  </process>

</definitions>
BPMN;
    }
}
