import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs) {
  return twMerge(clsx(inputs));
}

export function valueUpdater(updaterOrValue, ref) {
  ref.value =
    typeof updaterOrValue === "function"
      ? updaterOrValue(ref.value)
      : updaterOrValue;
}

export function getAiIcon(aiProvider) {
  const icons = {
    'OpenAI GPT-4': 'brain',
    'Claude AI': 'brain',
    'Gemini': 'sparkles',
    'Perplexity AI': 'search',
    'Mistral AI': 'zap'
  }
  return icons[aiProvider] || 'cpu'
}
