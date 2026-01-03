<?php

namespace Opscale\NovaMCP\MCP\Prompts;

use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

/**
 * Abstract FACTORS Prompt - A Playbook for AI Success
 *
 * THE FACTORS METHODOLOGY
 * =======================
 *
 * The core idea is simple: Break your problem into smaller pieces. Then prompt
 * and train each piece. Quickly.
 *
 * This isn't some groundbreaking technological revolution. It's a product approach
 * that puts the spotlight where it matters: iterating fast with feedback from people
 * who actually understand the problem. It's not about sinking months and massive
 * budgets into building complex systems. It's about finding value quickly, testing
 * fast, learning faster.
 *
 * THE TRUTH ABOUT AI IMPLEMENTATION
 * ==================================
 *
 * ✨ Your AI is only as good as your prompts.
 * ✨ Your prompts are only as good as your problem breakdown.
 * ✨ Your problem breakdown is only as good as your understanding of what you're
 *    actually trying to solve.
 *
 * So stop trying to throw one prompt at a big, fuzzy problem.
 *
 * Instead, follow the FACTORS. Break it down. Train it up. Tune each part.
 * And finally make AI work for you—not just at a demo, but in production.
 *
 * THE FRAMEWORK
 * =============
 *
 * Great AI implementation starts before you ever write a line of code. No complex
 * architecture. No orchestration layer. Just one core thing:
 *
 * A well-structured prompt + real-time feedback.
 *
 * We took inspiration from the COSTAR method but added what we believe is the most
 * critical element: F for Feedback. That gave us FACTORS:
 *
 * - F (Feedback): The loop. Every output should be reviewed and adjusted. You need
 *                 to know what worked, what didn't, and guide the AI step by step.
 *
 * - A (Audience): Who is this for? What do they care about? Speak their language.
 *
 * - C (Context): What background does the AI need to know to answer well?
 *
 * - T (Tone): Should it sound professional, casual, friendly, sarcastic? This
 *             affects everything.
 *
 * - O (Objective): What's the actual goal? Be specific, not fuzzy.
 *
 * - R (Response format): Bullet points? Table? JSON? Code snippet? Guide the structure.
 *
 * - S (Style): Do you want it bold and catchy? Soft and thoughtful? Match the style
 *              to the use case.
 *
 * USAGE
 * =====
 *
 * Extend this class and implement each abstract method to build a complete, structured
 * prompt. The handle() method will assemble all pieces into a cohesive prompt that
 * guides the AI effectively.
 *
 * Each element is crucial:
 * - Feedback: Automatically integrated from user input via MCP prompt arguments
 * - Audience: Define who this is for to shape language and depth
 * - Context: Provide background so the AI understands the situation
 * - Tone: Set how the message should feel
 * - Objective: State the specific goal with no ambiguity
 * - Response format: Specify the structure to get immediately usable output
 * - Style: Define presentation to ensure consistency and impact
 *
 * This approach ensures prompts are well-structured, clear, and effective by breaking
 * down complex problems into manageable, trainable pieces.
 */
abstract class FactorsPrompt extends Prompt
{
    /**
     * Define the prompt arguments schema
     *
     * This defines what arguments users can provide to the prompt,
     * primarily for the feedback loop in iterative refinement.
     *
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'feedback',
                description: 'Feedback from the previous iteration. What worked well? What needs improvement? Be specific about what to change.',
                required: false
            ),
        ];
    }

    /**
     * Handle the prompt request by assembling all FACTORS elements
     *
     * This method orchestrates the complete prompt by gathering each
     * element of the FACTORS framework and combining them into a
     * cohesive, structured prompt that guides the AI effectively.
     *
     * @param  array<string, mixed>  $arguments  Prompt arguments from the user
     */
    public function handle(array $arguments = []): string
    {
        $prompt = $this->buildPromptHeader();

        // F - Feedback: Set up the feedback loop with user input
        $feedback = $this->feedback($arguments);
        if (! empty($feedback)) {
            $prompt .= "\n## FEEDBACK LOOP\n{$feedback}\n";
        }

        // A - Audience: Define who this is for
        $audience = $this->audience();
        if (! empty($audience)) {
            $prompt .= "\n## AUDIENCE\n{$audience}\n";
        }

        // C - Context: Provide necessary background
        $context = $this->context();
        if (! empty($context)) {
            $prompt .= "\n## CONTEXT\n{$context}\n";
        }

        // T - Tone: Set the communication style
        $tone = $this->tone();
        if (! empty($tone)) {
            $prompt .= "\n## TONE\n{$tone}\n";
        }

        // O - Objective: Define the specific goal
        $objective = $this->objective();
        if (! empty($objective)) {
            $prompt .= "\n## OBJECTIVE\n{$objective}\n";
        }

        // R - Response format: Specify the output structure
        $responseFormat = $this->responseFormat();
        if (! empty($responseFormat)) {
            $prompt .= "\n## RESPONSE FORMAT\n{$responseFormat}\n";
        }

        // S - Style: Define the presentation approach
        $style = $this->style();
        if (! empty($style)) {
            $prompt .= "\n## STYLE\n{$style}\n";
        }

        $prompt .= $this->buildPromptFooter();

        return $prompt;
    }

    /**
     * F - Feedback: Build the feedback loop from user input
     *
     * This is the most critical element. It constructs a feedback section
     * dynamically based on user-provided feedback through MCP prompt arguments.
     *
     * The arguments array contains:
     * - 'feedback': Previous feedback from the user about prior outputs
     *
     * This method can be overridden to customize the feedback structure,
     * but the default implementation provides a solid foundation for
     * iterative refinement.
     *
     * @param  array<string, mixed>  $arguments  User-provided feedback
     * @return string The feedback loop instructions
     */
    protected function feedback(array $arguments): string
    {
        $userFeedback = $arguments['feedback'] ?? '';

        if (empty($userFeedback)) {
            return '';
        }

        $feedback = "**USER FEEDBACK**:\n\n";
        $feedback .= $userFeedback . "\n\n";
        $feedback .= "**IMPORTANT**: Apply this feedback to correct and improve the output.\n";

        return $feedback;
    }

    /**
     * A - Audience: Define who this output is for
     *
     * Specify the target audience, their knowledge level, what they care
     * about, and what language/terminology they understand. This shapes
     * everything else.
     *
     * @return string The audience definition
     *
     * @example
     * "Technical team leads who understand software architecture but need
     * business-friendly explanations for stakeholder presentations. They
     * value accuracy, clear structure, and actionable insights."
     */
    abstract protected function audience(): string;

    /**
     * C - Context: Provide necessary background information
     *
     * What background does the AI need to answer well? Include relevant
     * domain knowledge, project context, constraints, or any information
     * that helps the AI understand the situation.
     *
     * @return string The contextual background
     *
     * @example
     * "This is for a Laravel e-commerce application with 50K daily users.
     * The system uses Nova for admin panel, integrates with Stripe for
     * payments, and has complex inventory management. Performance and
     * data accuracy are critical business requirements."
     */
    abstract protected function context(): string;

    /**
     * T - Tone: Set the communication tone
     *
     * Should it sound professional, casual, friendly, authoritative,
     * empathetic? The tone affects how the message is received and
     * whether it resonates with the audience.
     *
     * @return string The tone specification
     *
     * @example
     * "Professional yet approachable. Use clear, confident language
     * without being condescending. Avoid jargon unless necessary,
     * and when used, provide brief explanations."
     */
    abstract protected function tone(): string;

    /**
     * O - Objective: Define the specific goal
     *
     * What's the actual goal? Be specific, not fuzzy. What does success
     * look like? What problem are we solving? What decision needs to be
     * made based on this output?
     *
     * @return string The specific objective
     *
     * @example
     * "Generate a step-by-step implementation plan that allows the
     * development team to add multi-currency support to the checkout
     * process within 2 weeks, without breaking existing payment flows."
     */
    abstract protected function objective(): string;

    /**
     * R - Response format: Specify the output structure
     *
     * Bullet points? Table? JSON? Code snippet? Numbered list? Paragraph?
     * Guide the structure to make the output immediately usable.
     *
     * @return string The response format specification
     *
     * @example
     * "Return as a structured markdown document with:
     * 1. Executive summary (3-5 sentences)
     * 2. Numbered action items with owner and timeline
     * 3. Risk assessment table (Risk, Impact, Mitigation)
     * 4. Code snippets in PHP with inline comments"
     */
    abstract protected function responseFormat(): string;

    /**
     * S - Style: Define the presentation style
     *
     * Do you want it bold and catchy? Soft and thoughtful? Data-driven?
     * Story-driven? Match the style to the use case and audience
     * expectations.
     *
     * @return string The style specification
     *
     * @example
     * "Direct and action-oriented. Start each section with the key
     * takeaway, then provide supporting details. Use active voice.
     * Include specific examples. Avoid marketing speak—focus on
     * practical value and concrete next steps."
     */
    abstract protected function style(): string;

    /**
     * Build the prompt header
     *
     * Override this method to customize the header section of your prompt.
     * By default, includes the prompt name and description.
     */
    protected function buildPromptHeader(): string
    {
        return "# {$this->name}\n\n{$this->description}\n";
    }

    /**
     * Build the prompt footer
     *
     * Override this method to customize the footer section of your prompt.
     * Useful for adding final instructions or reminders.
     */
    protected function buildPromptFooter(): string
    {
        return "\n\n---\n\nRemember: Review the output against the FEEDBACK criteria above and iterate if needed.\n";
    }

    /**
     * Get additional instructions or examples
     *
     * Override this method to provide specific examples, edge cases,
     * or additional instructions that help guide the AI.
     */
    protected function additionalInstructions(): string
    {
        return '';
    }
}
