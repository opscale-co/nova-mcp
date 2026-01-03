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

  <!-- User Registration Process -->
  <process id="user_registration" name="User Registration" isExecutable="true">
    <documentation>
      Complete workflow for registering a new user in the platform.
      This process handles user creation, email verification, and initial setup.
    </documentation>

    <startEvent id="start_registration" name="User Wants to Register">
      <documentation>A new person wants to create an account in the platform</documentation>
    </startEvent>

    <task id="collect_user_info" name="Collect User Information">
      <documentation>
        Gather required information from the user:
        - Full name
        - Email address
        - Password

        Business rules:
        - Email must be unique in the system
        - Password must meet security requirements
      </documentation>
    </task>

    <task id="validate_email" name="Validate Email Address">
      <documentation>
        Check that the email address:
        - Is properly formatted
        - Is not already registered
        - Is from an allowed domain (if restrictions apply)
      </documentation>
    </task>

    <exclusiveGateway id="gateway_email_valid" name="Email Valid?">
      <documentation>Decision point: Can we proceed with this email address?</documentation>
    </exclusiveGateway>

    <task id="create_user_record" name="Create User Record">
      <documentation>
        Create a new user record in the database with:
        - Name, email, encrypted password
        - email_verified_at set to NULL
        - created_at and updated_at timestamps

        This is a CRUD operation: Create on users table
      </documentation>
    </task>

    <task id="send_verification_email" name="Send Verification Email">
      <documentation>
        Send an email to the user with:
        - Welcome message
        - Email verification link
        - Getting started information

        This is a business action: sending automated communication
      </documentation>
    </task>

    <task id="show_error_message" name="Show Error Message">
      <documentation>
        Inform the user that registration failed because:
        - Email is already registered
        - Email format is invalid
        - Other validation errors

        User can try again with different information
      </documentation>
    </task>

    <endEvent id="end_registration_success" name="Registration Complete">
      <documentation>User has been successfully registered and can now verify their email</documentation>
    </endEvent>

    <endEvent id="end_registration_failed" name="Registration Failed">
      <documentation>User registration could not be completed due to validation errors</documentation>
    </endEvent>

    <!-- Sequence Flows -->
    <sequenceFlow id="flow1" sourceRef="start_registration" targetRef="collect_user_info" />
    <sequenceFlow id="flow2" sourceRef="collect_user_info" targetRef="validate_email" />
    <sequenceFlow id="flow3" sourceRef="validate_email" targetRef="gateway_email_valid" />
    <sequenceFlow id="flow4" name="Valid" sourceRef="gateway_email_valid" targetRef="create_user_record" />
    <sequenceFlow id="flow5" name="Invalid" sourceRef="gateway_email_valid" targetRef="show_error_message" />
    <sequenceFlow id="flow6" sourceRef="create_user_record" targetRef="send_verification_email" />
    <sequenceFlow id="flow7" sourceRef="send_verification_email" targetRef="end_registration_success" />
    <sequenceFlow id="flow8" sourceRef="show_error_message" targetRef="end_registration_failed" />
  </process>

  <!-- Email Verification Process -->
  <process id="email_verification" name="Email Verification" isExecutable="true">
    <documentation>
      Workflow for verifying a user's email address after registration.
      This confirms the user has access to the email address they provided.
    </documentation>

    <startEvent id="start_verification" name="User Clicks Verification Link">
      <documentation>User clicks the verification link sent to their email</documentation>
    </startEvent>

    <task id="validate_token" name="Validate Verification Token">
      <documentation>
        Check that the verification token:
        - Is valid and not expired
        - Matches a user in the system
        - Hasn't been used already
      </documentation>
    </task>

    <exclusiveGateway id="gateway_token_valid" name="Token Valid?">
      <documentation>Decision point: Is this a valid verification request?</documentation>
    </exclusiveGateway>

    <task id="mark_email_verified" name="Mark Email as Verified">
      <documentation>
        Update the user record:
        - Set email_verified_at to current timestamp
        - Update updated_at timestamp

        This is a CRUD operation: Update on users table
      </documentation>
    </task>

    <task id="send_welcome_email" name="Send Welcome Email">
      <documentation>
        Send confirmation email with:
        - Welcome to the platform
        - Next steps and resources
        - Support contact information

        This is a business action: sending automated communication
      </documentation>
    </task>

    <task id="show_verification_error" name="Show Verification Error">
      <documentation>
        Inform the user that verification failed because:
        - Token is invalid or expired
        - Email already verified
        - Other technical errors

        Provide option to request new verification link
      </documentation>
    </task>

    <endEvent id="end_verification_success" name="Verification Complete">
      <documentation>User's email has been successfully verified and account is fully active</documentation>
    </endEvent>

    <endEvent id="end_verification_failed" name="Verification Failed">
      <documentation>Email verification could not be completed</documentation>
    </endEvent>

    <!-- Sequence Flows -->
    <sequenceFlow id="verify_flow1" sourceRef="start_verification" targetRef="validate_token" />
    <sequenceFlow id="verify_flow2" sourceRef="validate_token" targetRef="gateway_token_valid" />
    <sequenceFlow id="verify_flow3" name="Valid" sourceRef="gateway_token_valid" targetRef="mark_email_verified" />
    <sequenceFlow id="verify_flow4" name="Invalid" sourceRef="gateway_token_valid" targetRef="show_verification_error" />
    <sequenceFlow id="verify_flow5" sourceRef="mark_email_verified" targetRef="send_welcome_email" />
    <sequenceFlow id="verify_flow6" sourceRef="send_welcome_email" targetRef="end_verification_success" />
    <sequenceFlow id="verify_flow7" sourceRef="show_verification_error" targetRef="end_verification_failed" />
  </process>

  <!-- User Profile Update Process -->
  <process id="profile_update" name="User Profile Update" isExecutable="true">
    <documentation>
      Workflow for updating user profile information.
      This is a simpler process demonstrating CRUD operations.
    </documentation>

    <startEvent id="start_update" name="User Wants to Update Profile">
      <documentation>User initiates profile update from their account settings</documentation>
    </startEvent>

    <task id="show_current_info" name="Show Current Information">
      <documentation>
        Display current user information:
        - Name
        - Email
        - Other profile fields

        This is a CRUD operation: Read on users table
      </documentation>
    </task>

    <task id="collect_changes" name="Collect Changes">
      <documentation>
        Allow user to modify:
        - Name
        - Email (requires re-verification)
        - Other profile information

        Note: Password changes use a separate secure process
      </documentation>
    </task>

    <task id="validate_changes" name="Validate Changes">
      <documentation>
        Verify that:
        - New email is unique (if changed)
        - All required fields are present
        - Data formats are correct
      </documentation>
    </task>

    <exclusiveGateway id="gateway_changes_valid" name="Changes Valid?">
      <documentation>Decision point: Can we save these changes?</documentation>
    </exclusiveGateway>

    <task id="update_user_record" name="Update User Record">
      <documentation>
        Update the user record in database:
        - Save modified fields
        - Update updated_at timestamp
        - If email changed, set email_verified_at to NULL

        This is a CRUD operation: Update on users table
      </documentation>
    </task>

    <task id="send_change_notification" name="Send Change Notification">
      <documentation>
        Notify user of profile changes via email:
        - Confirm what was changed
        - Security notification
        - Re-verification link if email changed

        This is a business action: sending automated communication
      </documentation>
    </task>

    <endEvent id="end_update_success" name="Update Complete">
      <documentation>User profile has been successfully updated</documentation>
    </endEvent>

    <endEvent id="end_update_failed" name="Update Failed">
      <documentation>Profile update could not be completed due to validation errors</documentation>
    </endEvent>

    <!-- Sequence Flows -->
    <sequenceFlow id="update_flow1" sourceRef="start_update" targetRef="show_current_info" />
    <sequenceFlow id="update_flow2" sourceRef="show_current_info" targetRef="collect_changes" />
    <sequenceFlow id="update_flow3" sourceRef="collect_changes" targetRef="validate_changes" />
    <sequenceFlow id="update_flow4" sourceRef="validate_changes" targetRef="gateway_changes_valid" />
    <sequenceFlow id="update_flow5" name="Valid" sourceRef="gateway_changes_valid" targetRef="update_user_record" />
    <sequenceFlow id="update_flow6" name="Invalid" sourceRef="gateway_changes_valid" targetRef="end_update_failed" />
    <sequenceFlow id="update_flow7" sourceRef="update_user_record" targetRef="send_change_notification" />
    <sequenceFlow id="update_flow8" sourceRef="send_change_notification" targetRef="end_update_success" />
  </process>

</definitions>
BPMN;
    }
}
