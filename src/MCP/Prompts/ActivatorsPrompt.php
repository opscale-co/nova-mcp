<?php

namespace Opscale\NovaMCP\MCP\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Prompt;

/**
 * Abstract ACTIVATORS Prompt - A Framework for Effective AI Prompts
 *
 * THE ACTIVATORS METHODOLOGY
 * ==========================
 *
 * ACTIVATORS is a framework for creating structured, effective AI prompts by
 * decomposing complex problems into manageable, trainable pieces.
 *
 * Each element serves a specific purpose:
 *
 * - A (Audience): Who this is for and what they care about
 * - C (Context): Background information the AI needs
 * - T (Tone): How the message should feel
 * - I (Intent): The general purpose goal to achieve
 * - V (Verification): External tools/processes to validate output correctness
 * - A (Appearance): How the content is presented
 * - T (Task): Sequence of steps to convert input to output
 * - O (Output): Form and structure of the final result
 * - R (Review): When to pause and confirm with the user
 * - S (Samples): Concrete input/output examples
 *
 * THE 5 KEYS OF ACTIVATORS
 * ========================
 *
 * 1. BUSINESS CONTEXT: Understand the Problem
 *    Your AI is only as good as your prompt. Your prompt is only as good as
 *    your understanding of the problem. If you don't understand the business
 *    problem, it doesn't matter how sophisticated your prompt is.
 *
 * 2. DIVISION: Concrete Problems, Not Abstract
 *    Instead of one giant prompt handling an abstract problem, decompose into
 *    specific prompts handling concrete steps. Concrete problems can be broken
 *    down into clear steps. Each small step is trainable, measurable, improvable.
 *
 * 3. DETAIL: Specificity Over Brevity
 *    Effective prompts are NOT simple phrases. They are extensive, well-defined
 *    descriptions. The more specific the prompt, the more precise and predictable
 *    the outputs. More detail = Less ambiguity = Better results.
 *
 * 4. HALLUCINATION CONTROL: Standards and External Validation
 *    Reduce errors by anchoring to established standards and using external
 *    validation. Use widely accepted standards (ISO, RFC, industry formats).
 *    External tools (linters, validators) provide objective feedback.
 *
 * 5. ITERATION: Experimental, Not Perfectionist
 *    The best AI results don't come from the first attempt. They come from
 *    many experimental iterations. Create, test, request review, identify
 *    failures, adjust, repeat.
 *
 * USAGE
 * =====
 *
 * Extend this class and implement each abstract method to build a complete,
 * structured prompt. The handle() method will assemble all pieces into a
 * cohesive prompt that guides the AI effectively.
 */
abstract class ActivatorsPrompt extends Prompt
{
    /**
     * Handle the prompt request by assembling all ACTIVATORS elements
     */
    public function handle(Request $request): string
    {
        $prompt = $this->buildPromptHeader();

        // A - Audience
        $audience = $this->audience();
        if (! empty($audience)) {
            $prompt .= "\n## AUDIENCE\n{$audience}\n";
        }

        // C - Context
        $context = $this->context();
        if (! empty($context)) {
            $prompt .= "\n## CONTEXT\n{$context}\n";
        }

        // T - Tone
        $tone = $this->tone();
        if (! empty($tone)) {
            $prompt .= "\n## TONE\n{$tone}\n";
        }

        // I - Intent
        $intent = $this->intent();
        if (! empty($intent)) {
            $prompt .= "\n## INTENT\n{$intent}\n";
        }

        // V - Verification
        $verification = $this->verification();
        if (! empty($verification)) {
            $prompt .= "\n## VERIFICATION\n{$verification}\n";
        }

        // A - Appearance
        $appearance = $this->appearance();
        if (! empty($appearance)) {
            $prompt .= "\n## APPEARANCE\n{$appearance}\n";
        }

        // T - Task
        $task = $this->task();
        if (! empty($task)) {
            $prompt .= "\n## TASK\n{$task}\n";
        }

        // O - Output
        $output = $this->output();
        if (! empty($output)) {
            $prompt .= "\n## OUTPUT\n{$output}\n";
        }

        // R - Review
        $review = $this->review();
        if (! empty($review)) {
            $prompt .= "\n## REVIEW\n{$review}\n";
        }

        // S - Samples
        $samples = $this->samples();
        if (! empty($samples)) {
            $prompt .= "\n## SAMPLES\n{$samples}\n";
        }

        $prompt .= $this->buildPromptFooter();

        return $prompt;
    }

    /**
     * A - Audience: Who this is for and what they care about
     *
     * Define the target audience including:
     * - Primary roles/users
     * - Knowledge level
     * - What they care about
     * - Appropriate language/terminology
     *
     * @return string The audience definition
     */
    abstract protected function audience(): string;

    /**
     * C - Context: Background information the AI needs
     *
     * Provide necessary context including:
     * - The business problem being solved
     * - Domain constraints and requirements
     * - Available resources or data sources
     * - Key requirements and limitations
     *
     * @return string The contextual background
     */
    abstract protected function context(): string;

    /**
     * T - Tone: How the message should feel
     *
     * Specify the communication tone:
     * - Overall tone characteristics
     * - What to do and what to avoid
     * - Consistency with audience expectations
     *
     * @return string The tone specification
     */
    abstract protected function tone(): string;

    /**
     * I - Intent: The general purpose goal to achieve
     *
     * Define the purpose including:
     * - Main goal of the prompt
     * - What success looks like
     * - Quality metrics or criteria
     *
     * @return string The intent/purpose
     */
    abstract protected function intent(): string;

    /**
     * V - Verification: External tools/processes to validate output
     *
     * Specify how to validate correctness:
     * - Automated checks (linters, validators)
     * - Manual review criteria
     * - What makes output "correct"
     * - Can be "No external verification available" if none exist
     *
     * @return string The verification approach
     */
    abstract protected function verification(): string;

    /**
     * A - Appearance: How the content is presented
     *
     * Define presentation characteristics:
     * - Organization and hierarchy
     * - Language style (active/passive voice)
     * - Format elements (headers, lists, tables)
     * - Length guidelines
     *
     * @return string The appearance specification
     */
    abstract protected function appearance(): string;

    /**
     * T - Task: Sequence of steps to convert input to output
     *
     * Define the step-by-step process:
     * - Ordered sequence of steps
     * - Each step: Input -> Action -> Output
     * - Clear logic from input to final output
     *
     * @return string The task steps
     */
    abstract protected function task(): string;

    /**
     * O - Output: Form and structure of the final result
     *
     * Specify the output format:
     * - Type of structure (list, table, JSON, prose, etc.)
     * - Required vs optional elements
     * - Specific data format if applicable
     * - Schema or template when relevant
     *
     * @return string The output specification
     */
    abstract protected function output(): string;

    /**
     * R - Review: When to pause and confirm with the user
     *
     * Define review checkpoints:
     * - Specific scenarios when to request review
     * - Why review is important at each point
     * - Example questions to ask
     *
     * @return string The review specification
     */
    abstract protected function review(): string;

    /**
     * S - Samples: Concrete input/output examples
     *
     * Provide examples including:
     * - At least 1 complete example
     * - Input clearly separated from output
     * - Realistic, not trivial examples
     *
     * @return string The samples/examples
     */
    abstract protected function samples(): string;

    /**
     * Build the prompt header
     */
    protected function buildPromptHeader(): string
    {
        return "# {$this->name}\n\n{$this->description}\n";
    }

    /**
     * Build the prompt footer with AI instructions
     */
    protected function buildPromptFooter(): string
    {
        return <<<'FOOTER'

---

## INSTRUCTIONS FOR AI

When executing this prompt, follow this sequence:

1. **Understand the Goal (INTENT)**: Read INTENT first to understand what you're trying to achieve
2. **Focus from Audience Perspective (AUDIENCE + CONTEXT)**: Adjust language and depth based on audience
3. **Understand the Output (OUTPUT)**: Know exactly what structure is expected
4. **Follow the Process (TASK)**: Follow each step sequentially, don't skip steps
5. **Format the Output (TONE + APPEARANCE)**: Apply tone and formatting guidelines
6. **Validate Against Examples (SAMPLES)**: Your output should match the structure of examples
7. **Execute Review Checkpoints (REVIEW)**: Pause and ask when specified

**Critical Reminders:**
- NEVER skip TASK steps - they define the logic
- ALWAYS match OUTPUT format exactly
- USE SAMPLES as quality benchmark
- APPLY TONE consistently
- EXECUTE REVIEW checkpoints - don't assume, validate

FOOTER;
    }
}
