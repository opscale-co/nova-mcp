<?php

namespace Opscale\NovaMCP\Contracts;

/**
 * Process Resolver - ISO 19510 (BPMN 2.0) Process Definition
 *
 * This interface defines a contract for generating business process
 * definitions in BPMN 2.0 XML format (ISO 19510 standard).
 */
interface ProcessResolver
{
    /**
     * Resolve and return the complete BPMN 2.0 XML representation of business processes.
     *
     * This method should generate a comprehensive BPMN 2.0 (ISO 19510) XML document
     * that describes the business processes of the application, including:
     * - Process definitions with business-friendly names and descriptions
     * - Tasks, events, and gateways that make up each process
     * - Sequence flows showing the order of activities
     * - Data objects and associations
     * - Pools and lanes for organizational structure
     * - Documentation elements explaining each process step
     *
     * The XML must be valid BPMN 2.0 and include proper documentation
     * for all process elements.
     *
     * @return string The complete BPMN 2.0 XML document
     *
     * @example
     * <?xml version="1.0" encoding="UTF-8"?>
     * <definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL"
     *              xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI"
     *              id="Definitions_1"
     *              targetNamespace="http://example.com/bpmn">
     *
     *   <process id="order_fulfillment" name="Order Fulfillment Process">
     *     <documentation>Complete workflow for processing customer orders from placement to delivery</documentation>
     *
     *     <startEvent id="start_order" name="Order Placed">
     *       <documentation>Customer places a new order in the system</documentation>
     *     </startEvent>
     *
     *     <task id="validate_order" name="Validate Order">
     *       <documentation>Check if all order details are correct and items are in stock</documentation>
     *     </task>
     *
     *     <exclusiveGateway id="gateway_valid" name="Order Valid?">
     *       <documentation>Decision point to determine if order can be processed</documentation>
     *     </exclusiveGateway>
     *
     *     <task id="process_payment" name="Process Payment">
     *       <documentation>Charge customer's payment method for the order total</documentation>
     *     </task>
     *
     *     <task id="prepare_shipment" name="Prepare Shipment">
     *       <documentation>Package items and prepare for delivery to customer</documentation>
     *     </task>
     *
     *     <endEvent id="end_order" name="Order Completed">
     *       <documentation>Order has been successfully fulfilled and delivered</documentation>
     *     </endEvent>
     *
     *     <sequenceFlow id="flow1" sourceRef="start_order" targetRef="validate_order" />
     *     <sequenceFlow id="flow2" sourceRef="validate_order" targetRef="gateway_valid" />
     *     <sequenceFlow id="flow3" name="Valid" sourceRef="gateway_valid" targetRef="process_payment" />
     *     <sequenceFlow id="flow4" sourceRef="process_payment" targetRef="prepare_shipment" />
     *     <sequenceFlow id="flow5" sourceRef="prepare_shipment" targetRef="end_order" />
     *   </process>
     * </definitions>
     */
    public function resolve(): string;
}
