import { useSiteConfig } from "../lib/fastfix";

/**
 * Affiche une liste éditable depuis wp-admin → FastFix → Contenus des pages.
 *
 * Chaque liste est stockée comme du texte (une ligne = un élément, colonnes
 * séparées par « | ») et renvoyée par l'API sous forme de tableau de lignes.
 * Tant que WordPress n'a rien renvoyé — ou si l'API est injoignable — c'est
 * la liste `fallback` livrée avec le site qui s'affiche.
 */

type Row = string[];

interface Props {
  /** Clé de contenu, ex. "home.what_to_repair.pills" */
  contentKey: string;
  /** Liste affichée par défaut, au format [[col1, col2], ...] */
  fallback: Row[];
  variant: "pill" | "steps" | "cards" | "grades" | "compare" | "badges" | "priceRows";
  /** Icônes SVG (chemins) réutilisées par position, pour les variantes qui en affichent */
  icons?: string[];
  /** Lien appliqué aux éléments cliquables */
  href?: string;
}

const FALLBACK_ICON =
  "M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z";

export default function ContentList({ contentKey, fallback, variant, icons = [], href }: Props) {
  const config = useSiteConfig();
  const remote = config?.content?.[contentKey];
  const rows: Row[] = Array.isArray(remote) && remote.length > 0 ? (remote as unknown as Row[]) : fallback;

  const icon = (i: number) => icons[i] ?? icons[icons.length - 1] ?? FALLBACK_ICON;

  if (variant === "pill") {
    return (
      <div data-reveal className="flex flex-wrap justify-center gap-3 mb-12">
        {rows.map((row, i) => (
          <a key={row[0]} href={href ?? "/reparations"} className="pill">
            <svg className="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" d={icon(i)} />
            </svg>
            {row[0]}
          </a>
        ))}
      </div>
    );
  }

  if (variant === "badges") {
    return (
      <div className="flex flex-wrap gap-2 mb-6">
        {rows.map((row) => (
          <span key={row[0]} className="badge text-xs">
            {row[0]}
          </span>
        ))}
      </div>
    );
  }

  if (variant === "grades") {
    return (
      <div data-reveal-children className="mt-10 grid sm:grid-cols-3 gap-4 max-w-2xl">
        {rows.map((row) => (
          <div key={row[0]} className="flex items-start gap-3 bg-white rounded-xl p-4 border border-border">
            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 font-display font-bold text-primary text-xs flex-shrink-0">
              {row[0]}
            </span>
            <span className="text-xs text-text-muted leading-relaxed">{row[1]}</span>
          </div>
        ))}
      </div>
    );
  }

  if (variant === "steps") {
    return (
      <div data-reveal-children className="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {rows.map((row, i) => (
          <div key={row[0]} className="card p-5 text-center relative">
            <div className="absolute -top-3 left-1/2 -translate-x-1/2 flex h-7 w-7 items-center justify-center rounded-full bg-primary text-white font-display font-bold text-xs">
              {i + 1}
            </div>
            <h3 className="font-display text-sm font-bold mb-1 mt-3">{row[0]}</h3>
            <p className="text-xs text-text-muted">{row[1]}</p>
          </div>
        ))}
      </div>
    );
  }

  if (variant === "priceRows") {
    // [intervention, prix, durée]
    return (
      <div data-reveal-children className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-w-4xl mx-auto">
        {rows.map((row) => (
          <div
            key={row[0]}
            className="flex items-center justify-between p-4 rounded-xl border border-border bg-white hover:border-primary/30 transition-colors"
          >
            <div>
              <span className="font-display font-semibold text-sm text-text">{row[0]}</span>
              <span className="block text-xs text-text-muted mt-0.5">{row[2]}</span>
            </div>
            <span className="font-display font-bold text-sm text-primary whitespace-nowrap">{row[1]}</span>
          </div>
        ))}
      </div>
    );
  }

  if (variant === "compare") {
    return (
      <tbody className="divide-y divide-border">
        {rows.map((row) => (
          <tr key={row[0]}>
            <td className="p-4 font-medium text-text">{row[0]}</td>
            <td className="p-4 text-center text-primary font-medium">{row[1]}</td>
            <td className="p-4 text-center text-text-muted">{row[2]}</td>
          </tr>
        ))}
      </tbody>
    );
  }

  // variant === "cards"
  return (
    <div data-reveal-children className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      {rows.map((row, i) => (
        <div key={row[0]} className="card p-7">
          <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 mb-4">
            <svg className="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" d={icon(i)} />
            </svg>
          </div>
          <h3 className="font-display text-base font-bold mb-2">{row[0]}</h3>
          <p className="text-sm text-text-muted leading-relaxed">{row[1]}</p>
        </div>
      ))}
    </div>
  );
}
