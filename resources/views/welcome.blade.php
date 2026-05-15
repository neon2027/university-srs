<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Welcome') }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        <!-- Styles -->
        <style>
            @layer properties{@supports (((-webkit-hyphens:none)) and (not (margin-trim:inline))) or ((-moz-orient:inline) and (not (color:rgb(from red r g b)))){*,:before,:after,::backdrop{--tw-translate-x:0;--tw-translate-y:0;--tw-translate-z:0;--tw-rotate-x:initial;--tw-rotate-y:initial;--tw-rotate-z:initial;--tw-skew-x:initial;--tw-skew-y:initial;--tw-space-x-reverse:0;--tw-border-style:solid;--tw-leading:initial;--tw-font-weight:initial;--tw-shadow:0 0 #0000;--tw-shadow-color:initial;--tw-shadow-alpha:100%;--tw-inset-shadow:0 0 #0000;--tw-inset-shadow-color:initial;--tw-inset-shadow-alpha:100%;--tw-ring-color:initial;--tw-ring-shadow:0 0 #0000;--tw-inset-ring-color:initial;--tw-inset-ring-shadow:0 0 #0000;--tw-ring-inset:initial;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-offset-shadow:0 0 #0000;--tw-blur:initial;--tw-brightness:initial;--tw-contrast:initial;--tw-grayscale:initial;--tw-hue-rotate:initial;--tw-invert:initial;--tw-opacity:initial;--tw-saturate:initial;--tw-sepia:initial;--tw-drop-shadow:initial;--tw-drop-shadow-color:initial;--tw-drop-shadow-alpha:100%;--tw-drop-shadow-size:initial;--tw-duration:initial;--tw-content:""}}}@layer theme{:root,:host{--font-sans:"Instrument Sans", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";--font-serif:ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;--font-mono:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;--color-red-50:oklch(97.1% .013 17.38);--color-red-100:oklch(93.6% .032 17.717);--color-red-200:oklch(88.5% .062 18.334);--color-red-300:oklch(80.8% .114 19.571);--color-red-400:oklch(70.4% .191 22.216);--color-red-500:oklch(63.7% .237 25.331);--color-red-600:oklch(57.7% .245 27.325);--color-red-700:oklch(50.5% .213 27.518);--color-red-800:oklch(44.4% .177 26.899);--color-red-900:oklch(39.6% .141 25.723);--color-red-950:oklch(25.8% .092 26.042);--color-orange-50:oklch(98% .016 73.684);--color-orange-100:oklch(95.4% .038 75.164);--color-orange-200:oklch(90.1% .076 70.697);--color-orange-300:oklch(83.7% .128 66.29);--color-orange-400:oklch(75% .183 55.934);--color-orange-500:oklch(70.5% .213 47.604);--color-orange-600:oklch(64.6% .222 41.116);--color-orange-700:oklch(55.3% .195 38.402);--color-orange-800:oklch(47% .157 37.304);--color-orange-900:oklch(40.8% .123 38.172);--color-orange-950:oklch(26.6% .079 36.259);--color-amber-50:oklch(98.7% .022 95.277);--color-amber-100:oklch(96.2% .059 95.617);--color-amber-200:oklch(92.4% .12 95.746);--color-amber-300:oklch(87.9% .169 91.605);--color-amber-400:oklch(82.8% .189 84.429);--color-amber-500:oklch(76.9% .188 70.08);--color-amber-600:oklch(66.6% .179 58.318);--color-amber-700:oklch(55.5% .163 48.998);--color-amber-800:oklch(47.3% .137 46.201);--color-amber-900:oklch(41.4% .112 45.904);--color-amber-950:oklch(27.9% .077 45.635);--color-yellow-50:oklch(98.7% .026 102.212);--color-yellow-100:oklch(97.3% .071 103.193);--color-yellow-200:oklch(94.5% .129 101.54);--color-yellow-300:oklch(90.5% .182 98.111);--color-yellow-400:oklch(85.2% .199 91.936);--color-yellow-500:oklch(79.5% .184 86.047);--color-yellow-600:oklch(68.1% .162 75.834);--color-yellow-700:oklch(55.4% .135 66.442);--color-yellow-800:oklch(47.6% .114 61.907);--color-yellow-900:oklch(42.1% .095 57.708);--color-yellow-950:oklch(28.6% .066 53.813);--color-lime-50:oklch(98.6% .031 120.757);--color-lime-100:oklch(96.7% .067 122.328);--color-lime-200:oklch(93.8% .127 124.321);--color-lime-300:oklch(89.7% .196 126.665);--color-lime-400:oklch(84.1% .238 128.85);--color-lime-500:oklch(76.8% .233 130.85);--color-lime-600:oklch(64.8% .2 131.684);--color-lime-700:oklch(53.2% .157 131.589);--color-lime-800:oklch(45.3% .124 130.933);--color-lime-900:oklch(40.5% .101 131.063);--color-lime-950:oklch(27.4% .072 132.109);--color-green-50:oklch(98.2% .018 155.826);--color-green-100:oklch(96.2% .044 156.743);--color-green-200:oklch(92.5% .084 155.995);--color-green-300:oklch(87.1% .15 154.449);--color-green-400:oklch(79.2% .209 151.711);--color-green-500:oklch(72.3% .219 149.579);--color-green-600:oklch(62.7% .194 149.214);--color-green-700:oklch(52.7% .154 150.069);--color-green-800:oklch(44.8% .119 151.328);--color-green-900:oklch(39.3% .095 152.535);--color-green-950:oklch(26.6% .065 152.934);--color-emerald-50:oklch(97.9% .021 166.113);--color-emerald-100:oklch(95% .052 163.051);--color-emerald-200:oklch(90.5% .093 164.15);--color-emerald-300:oklch(84.5% .143 164.978);--color-emerald-400:oklch(76.5% .177 163.223);--color-emerald-500:oklch(69.6% .17 162.48);--color-emerald-600:oklch(59.6% .145 163.225);--color-emerald-700:oklch(50.8% .118 165.612);--color-emerald-800:oklch(43.2% .095 166.913);--color-emerald-900:oklch(37.8% .077 168.94);--color-emerald-950:oklch(26.2% .051 172.552);--color-teal-50:oklch(98.4% .014 180.72);--color-teal-100:oklch(95.3% .051 180.801);--color-teal-200:oklch(91% .096 180.426);--color-teal-300:oklch(85.5% .138 181.071);--color-teal-400:oklch(77.7% .152 181.912);--color-teal-500:oklch(70.4% .14 182.503);--color-teal-600:oklch(60% .118 184.704);--color-teal-700:oklch(51.1% .096 186.391);--color-teal-800:oklch(43.7% .078 188.216);--color-teal-900:oklch(38.6% .063 188.416);--color-teal-950:oklch(27.7% .046 192.524);--color-cyan-50:oklch(98.4% .019 200.873);--color-cyan-100:oklch(95.6% .045 203.388);--color-cyan-200:oklch(91.7% .08 205.041);--color-cyan-300:oklch(86.5% .127 207.078);--color-cyan-400:oklch(78.9% .154 211.53);--color-cyan-500:oklch(71.5% .143 215.221);--color-cyan-600:oklch(60.9% .126 221.723);--color-cyan-700:oklch(52% .105 223.128);--color-cyan-800:oklch(45% .085 224.283);--color-cyan-900:oklch(39.8% .07 227.392);--color-cyan-950:oklch(30.2% .056 229.695);--color-sky-50:oklch(97.7% .013 236.62);--color-sky-100:oklch(95.1% .026 236.824);--color-sky-200:oklch(90.1% .058 230.902);--color-sky-300:oklch(82.8% .111 230.318);--color-sky-400:oklch(74.6% .16 232.661);--color-sky-500:oklch(68.5% .169 237.323);--color-sky-600:oklch(58.8% .158 241.966);--color-sky-700:oklch(50% .134 242.749);--color-sky-800:oklch(44.3% .11 240.79);--color-sky-900:oklch(39.1% .09 240.876);--color-sky-950:oklch(29.3% .066 243.157);--color-blue-50:oklch(97% .014 254.604);--color-blue-100:oklch(93.2% .032 255.585);--color-blue-200:oklch(88.2% .059 254.128);--color-blue-300:oklch(80.9% .105 251.813);--color-blue-400:oklch(70.7% .165 254.624);--color-blue-500:oklch(62.3% .214 259.815);--color-blue-600:oklch(54.6% .245 262.881);--color-blue-700:oklch(48.8% .243 264.376);--color-blue-800:oklch(42.4% .199 265.638);--color-blue-900:oklch(37.9% .146 265.522);--color-blue-950:oklch(28.2% .091 267.935);--color-indigo-50:oklch(96.2% .018 272.314);--color-indigo-100:oklch(93% .034 272.788);--color-indigo-200:oklch(87% .065 274.039);--color-indigo-300:oklch(78.5% .115 274.713);--color-indigo-400:oklch(67.3% .182 276.935);--color-indigo-500:oklch(58.5% .233 277.117);--color-indigo-600:oklch(51.1% .262 276.966);--color-indigo-700:oklch(45.7% .24 277.023);--color-indigo-800:oklch(39.8% .195 277.366);--color-indigo-900:oklch(35.9% .144 278.697);--color-indigo-950:oklch(25.7% .09 281.288);--color-violet-50:oklch(96.9% .016 293.756);--color-violet-100:oklch(94.3% .029 294.588);--color-violet-200:oklch(89.4% .057 293.283);--color-violet-300:oklch(81.1% .111 293.571);--color-violet-400:oklch(70.2% .183 293.541);--color-violet-500:oklch(60.6% .25 292.717);--color-violet-600:oklch(54.1% .281 293.009);--color-violet-700:oklch(49.1% .27 292.581);--color-violet-800:oklch(43.2% .232 292.759);--color-violet-900:oklch(38% .189 293.745);--color-violet-950:oklch(28.3% .141 291.089);--color-purple-50:oklch(97.7% .014 308.299);--color-purple-100:oklch(94.6% .033 307.174);--color-purple-200:oklch(90.2% .063 306.703);--color-purple-300:oklch(82.7% .119 306.383);--color-purple-400:oklch(71.4% .203 305.504);--color-purple-500:oklch(62.7% .265 303.9);--color-purple-600:oklch(55.8% .288 302.321);--color-purple-700:oklch(49.6% .265 301.924);--color-purple-800:oklch(43.8% .218 303.724);--color-purple-900:oklch(38.1% .176 304.987);--color-purple-950:oklch(29.1% .149 302.717);--color-fuchsia-50:oklch(97.7% .017 320.058);--color-fuchsia-100:oklch(95.2% .037 318.852);--color-fuchsia-200:oklch(90.3% .076 319.62);--color-fuchsia-300:oklch(83.3% .145 321.434);--color-fuchsia-400:oklch(74% .238 322.16);--color-fuchsia-500:oklch(66.7% .295 322.15);--color-fuchsia-600:oklch(59.1% .293 322.896);--color-fuchsia-700:oklch(51.8% .253 323.949);--color-fuchsia-800:oklch(45.2% .211 324.591);--color-fuchsia-900:oklch(40.1% .17 325.612);--color-fuchsia-950:oklch(29.3% .136 325.661);--color-pink-50:oklch(97.1% .014 343.198);--color-pink-100:oklch(94.8% .028 342.258);--color-pink-200:oklch(89.9% .061 343.231);--color-pink-300:oklch(82.3% .12 346.018);--color-pink-400:oklch(71.8% .202 349.761);--color-pink-500:oklch(65.6% .241 354.308);--color-pink-600:oklch(59.2% .249 .584);--color-pink-700:oklch(52.5% .223 3.958);--color-pink-800:oklch(45.9% .187 3.815);--color-pink-900:oklch(40.8% .153 2.432);--color-pink-950:oklch(28.4% .109 3.907);--color-rose-50:oklch(96.9% .015 12.422);--color-rose-100:oklch(94.1% .03 12.58);--color-rose-200:oklch(89.2% .058 10.001);--color-rose-300:oklch(81% .117 11.638);--color-rose-400:oklch(71.2% .194 13.428);--color-rose-500:oklch(64.5% .246 16.439);--color-rose-600:oklch(58.6% .253 17.585);--color-rose-700:oklch(51.4% .222 16.935);--color-rose-800:oklch(45.5% .188 13.697);--color-rose-900:oklch(41% .159 10.272);--color-rose-950:oklch(27.1% .105 12.094);--color-slate-50:oklch(98.4% .003 247.858);--color-slate-100:oklch(96.8% .007 247.896);--color-slate-200:oklch(92.9% .013 255.508);--color-slate-300:oklch(86.9% .022 252.894);--color-slate-400:oklch(70.4% .04 256.788);--color-slate-500:oklch(55.4% .046 257.417);--color-slate-600:oklch(44.6% .043 257.281);--color-slate-700:oklch(37.2% .044 257.287);--color-slate-800:oklch(27.9% .041 260.031);--color-slate-900:oklch(20.8% .042 265.755);--color-slate-950:oklch(12.9% .042 264.695);--color-gray-50:oklch(98.5% .002 247.839);--color-gray-100:oklch(96.7% .003 264.542);--color-gray-200:oklch(92.8% .006 264.531);--color-gray-300:oklch(87.2% .01 258.338);--color-gray-400:oklch(70.7% .022 261.325);--color-gray-500:oklch(55.1% .027 264.364);--color-gray-600:oklch(44.6% .03 256.802);--color-gray-700:oklch(37.3% .034 259.733);--color-gray-800:oklch(27.8% .033 256.848);--color-gray-900:oklch(21% .034 264.665);--color-gray-950:oklch(13% .028 261.692);--color-zinc-50:oklch(98.5% 0 0);--color-zinc-100:oklch(96.7% .001 286.375);--color-zinc-200:oklch(92% .004 286.32);--color-zinc-300:oklch(87.1% .006 286.286);--color-zinc-400:oklch(70.5% .015 286.067);--color-zinc-500:oklch(55.2% .016 285.938);--color-zinc-600:oklch(44.2% .017 285.786);--color-zinc-700:oklch(37% .013 285.805);--color-zinc-800:oklch(27.4% .006 286.033);--color-zinc-900:oklch(21% .006 285.885);--color-zinc-950:oklch(14.1% .005 285.823);--color-neutral-50:oklch(98.5% 0 0);--color-neutral-100:oklch(97% 0 0);--color-neutral-200:oklch(92.2% 0 0);--color-neutral-300:oklch(87% 0 0);--color-neutral-400:oklch(70.8% 0 0);--color-neutral-500:oklch(55.6% 0 0);--color-neutral-600:oklch(43.9% 0 0);--color-neutral-700:oklch(37.1% 0 0);--color-neutral-800:oklch(26.9% 0 0);--color-neutral-900:oklch(20.5% 0 0);--color-neutral-950:oklch(14.5% 0 0);--color-stone-50:oklch(98.5% .001 106.423);--color-stone-100:oklch(97% .001 106.424);--color-stone-200:oklch(92.3% .003 48.717);--color-stone-300:oklch(86.9% .005 56.366);--color-stone-400:oklch(70.9% .01 56.259);--color-stone-500:oklch(55.3% .013 58.071);--color-stone-600:oklch(44.4% .011 73.639);--color-stone-700:oklch(37.4% .01 67.558);--color-stone-800:oklch(26.8% .007 34.298);--color-stone-900:oklch(21.6% .006 56.043);--color-stone-950:oklch(14.7% .004 49.25);--color-black:#000;--color-white:#fff;--spacing:.25rem;--breakpoint-sm:40rem;--breakpoint-md:48rem;--breakpoint-lg:64rem;--breakpoint-xl:80rem;--breakpoint-2xl:96rem;--container-3xs:16rem;--container-2xs:18rem;--container-xs:20rem;--container-sm:24rem;--container-md:28rem;--container-lg:32rem;--container-xl:36rem;--container-2xl:42rem;--container-3xl:48rem;--container-4xl:56rem;--container-5xl:64rem;--container-6xl:72rem;--container-7xl:80rem;--text-xs:.75rem;--text-xs--line-height:calc(1 / .75);--text-sm:.875rem;--text-sm--line-height:calc(1.25 / .875);--text-base:1rem;--text-base--line-height: 1.5 ;--text-lg:1.125rem;--text-lg--line-height:calc(1.75 / 1.125);--text-xl:1.25rem;--text-xl--line-height:calc(1.75 / 1.25);--text-2xl:1.5rem;--text-2xl--line-height:calc(2 / 1.5);--text-3xl:1.875rem;--text-3xl--line-height: 1.2 ;--text-4xl:2.25rem;--text-4xl--line-height:calc(2.5 / 2.25);--text-5xl:3rem;--text-5xl--line-height:1;--text-6xl:3.75rem;--text-6xl--line-height:1;--text-7xl:4.5rem;--text-7xl--line-height:1;--text-8xl:6rem;--text-8xl--line-height:1;--text-9xl:8rem;--text-9xl--line-height:1;--font-weight-thin:100;--font-weight-extralight:200;--font-weight-light:300;--font-weight-normal:400;--font-weight-medium:500;--font-weight-semibold:600;--font-weight-bold:700;--font-weight-extrabold:800;--font-weight-black:900;--tracking-tighter:-.05em;--tracking-tight:-.025em;--tracking-normal:0em;--tracking-wide:.025em;--tracking-wider:.05em;--tracking-widest:.1em;--leading-tight:1.25;--leading-snug:1.375;--leading-normal:1.5;--leading-relaxed:1.625;--leading-loose:2;--radius-xs:.125rem;--radius-sm:.25rem;--radius-md:.375rem;--radius-lg:.5rem;--radius-xl:.75rem;--radius-2xl:1rem;--radius-3xl:1.5rem;--radius-4xl:2rem;--shadow-2xs:0 1px #0000000d;--shadow-xs:0 1px 2px 0 #0000000d;--shadow-sm:0 1px 3px 0 #0000001a, 0 1px 2px -1px #0000001a;--shadow-md:0 4px 6px -1px #0000001a, 0 2px 4px -2px #0000001a;--shadow-lg:0 10px 15px -3px #0000001a, 0 4px 6px -4px #0000001a;--shadow-xl:0 20px 25px -5px #0000001a, 0 8px 10px -6px #0000001a;--shadow-2xl:0 25px 50px -12px #00000040;--inset-shadow-2xs:inset 0 1px #0000000d;--inset-shadow-xs:inset 0 1px 1px #0000000d;--inset-shadow-sm:inset 0 2px 4px #0000000d;--drop-shadow-xs:0 1px 1px #0000000d;--drop-shadow-sm:0 1px 2px #00000026;--drop-shadow-md:0 3px 3px #0000001f;--drop-shadow-lg:0 4px 4px #00000026;--drop-shadow-xl:0 9px 7px #0000001a;--drop-shadow-2xl:0 25px 25px #00000026;--ease-in:cubic-bezier(.4, 0, 1, 1);--ease-out:cubic-bezier(0, 0, .2, 1);--ease-in-out:cubic-bezier(.4, 0, .2, 1);--animate-spin:spin 1s linear infinite;--animate-ping:ping 1s cubic-bezier(0, 0, .2, 1) infinite;--animate-pulse:pulse 2s cubic-bezier(.4, 0, .6, 1) infinite;--animate-bounce:bounce 1s infinite;--blur-xs:4px;--blur-sm:8px;--blur-md:12px;--blur-lg:16px;--blur-xl:24px;--blur-2xl:40px;--blur-3xl:64px;--perspective-dramatic:100px;--perspective-near:300px;--perspective-normal:500px;--perspective-midrange:800px;--perspective-distant:1200px;--aspect-video:16 / 9;--default-transition-duration:.15s;--default-transition-timing-function:cubic-bezier(.4, 0, .2, 1);--default-font-family:var(--font-sans);--default-mono-font-family:var(--font-mono)}}@layer base{*,:after,:before,::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0}::file-selector-button{box-sizing:border-box;border:0 solid;margin:0;padding:0}html,:host{-webkit-text-size-adjust:100%;tab-size:4;line-height:1.5;font-family:var(--default-font-family,ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");font-feature-settings:var(--default-font-feature-settings,normal);font-variation-settings:var(--default-font-variation-settings,normal);-webkit-tap-highlight-color:transparent}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;-webkit-text-decoration:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:var(--default-mono-font-family,ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace);font-feature-settings:var(--default-mono-font-feature-settings,normal);font-variation-settings:var(--default-mono-font-variation-settings,normal);font-size:1em}small{font-size:80%}sub,sup{vertical-align:baseline;font-size:75%;line-height:0;position:relative}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}:-moz-focusring{outline:auto}progress{vertical-align:baseline}summary{display:list-item}ol,ul,menu{list-style:none}img,svg,video,canvas,audio,iframe,embed,object{vertical-align:middle;display:block}img,video{max-width:100%;height:auto}button,input,select,optgroup,textarea{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}::file-selector-button{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}:where(select:is([multiple],[size])) optgroup{font-weight:bolder}:where(select:is([multiple],[size])) optgroup option{padding-inline-start:20px}::file-selector-button{margin-inline-end:4px}::placeholder{opacity:1}@supports (not ((-webkit-appearance:-apple-pay-button))) or (contain-intrinsic-size:1px){::placeholder{color:currentColor}@supports (color:color-mix(in lab,red,red)){::placeholder{color:color-mix(in oklab,currentcolor 50%,transparent)}}}textarea{resize:vertical}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-date-and-time-value{min-height:1lh;text-align:inherit}::-webkit-datetime-edit{display:inline-flex}::-webkit-datetime-edit-fields-wrapper{padding:0}::-webkit-datetime-edit{padding-block:0}::-webkit-datetime-edit-year-field{padding-block:0}::-webkit-datetime-edit-month-field{padding-block:0}::-webkit-datetime-edit-day-field{padding-block:0}::-webkit-datetime-edit-hour-field{padding-block:0}::-webkit-datetime-edit-minute-field{padding-block:0}::-webkit-datetime-edit-second-field{padding-block:0}::-webkit-datetime-edit-millisecond-field{padding-block:0}::-webkit-datetime-edit-meridiem-field{padding-block:0}::-webkit-calendar-picker-indicator{line-height:1}:-moz-ui-invalid{box-shadow:none}button,input:where([type=button],[type=reset],[type=submit]){appearance:button}::file-selector-button{appearance:button}::-webkit-inner-spin-button{height:auto}::-webkit-outer-spin-button{height:auto}[hidden]:where(:not([hidden=until-found])){display:none!important}}@layer components;@layer utilities{.absolute{position:absolute}.relative{position:relative}.static{position:static}.inset-0{inset:calc(var(--spacing) * 0)}.start{inset-inline-start:var(--spacing)}.ms-1{margin-inline-start:calc(var(--spacing) * 1)}.-mt-\[6\.6rem\]{margin-top:-6.6rem}.-mb-px{margin-bottom:-1px}.mb-1{margin-bottom:calc(var(--spacing) * 1)}.mb-2{margin-bottom:calc(var(--spacing) * 2)}.mb-4{margin-bottom:calc(var(--spacing) * 4)}.mb-6{margin-bottom:calc(var(--spacing) * 6)}.-ml-8{margin-left:calc(var(--spacing) * -8)}.contents{display:contents}.flex{display:flex}.hidden{display:none}.inline-block{display:inline-block}.inline-flex{display:inline-flex}.table{display:table}.aspect-\[335\/364\]{aspect-ratio:335/364}.h-1{height:calc(var(--spacing) * 1)}.h-1\.5{height:calc(var(--spacing) * 1.5)}.h-2{height:calc(var(--spacing) * 2)}.h-2\.5{height:calc(var(--spacing) * 2.5)}.h-3{height:calc(var(--spacing) * 3)}.h-3\.5{height:calc(var(--spacing) * 3.5)}.h-14{height:calc(var(--spacing) * 14)}.h-14\.5{height:calc(var(--spacing) * 14.5)}.min-h-screen{min-height:100vh}.w-1{width:calc(var(--spacing) * 1)}.w-1\.5{width:calc(var(--spacing) * 1.5)}.w-2{width:calc(var(--spacing) * 2)}.w-2\.5{width:calc(var(--spacing) * 2.5)}.w-3{width:calc(var(--spacing) * 3)}.w-3\.5{width:calc(var(--spacing) * 3.5)}.w-\[438px\]{width:438px}.w-full{width:100%}.max-w-\[335px\]{max-width:335px}.max-w-none{max-width:none}.flex-1{flex:1}.shrink-0{flex-shrink:0}.translate-y-0{--tw-translate-y:calc(var(--spacing) * 0);translate:var(--tw-translate-x) var(--tw-translate-y)}.transform{transform:var(--tw-rotate-x,) var(--tw-rotate-y,) var(--tw-rotate-z,) var(--tw-skew-x,) var(--tw-skew-y,)}.flex-col{flex-direction:column}.flex-col-reverse{flex-direction:column-reverse}.items-center{align-items:center}.justify-center{justify-content:center}.justify-end{justify-content:flex-end}.gap-3{gap:calc(var(--spacing) * 3)}.gap-4{gap:calc(var(--spacing) * 4)}:where(.space-x-1>:not(:last-child)){--tw-space-x-reverse:0;margin-inline-start:calc(calc(var(--spacing) * 1) * var(--tw-space-x-reverse));margin-inline-end:calc(calc(var(--spacing) * 1) * calc(1 - var(--tw-space-x-reverse)))}.overflow-hidden{overflow:hidden}.rounded-full{border-radius:3.40282e38px}.rounded-sm{border-radius:var(--radius-sm)}.rounded-ee-lg{border-end-end-radius:var(--radius-lg)}.rounded-es-lg{border-end-start-radius:var(--radius-lg)}.rounded-t-lg{border-top-left-radius:var(--radius-lg);border-top-right-radius:var(--radius-lg)}.rounded-br-lg{border-bottom-right-radius:var(--radius-lg)}.rounded-bl-lg{border-bottom-left-radius:var(--radius-lg)}.border{border-style:var(--tw-border-style);border-width:1px}.border-\[\#19140035\]{border-color:#19140035}.border-\[\#e3e3e0\]{border-color:#e3e3e0}.border-black{border-color:var(--color-black)}.border-transparent{border-color:#0000}.bg-\[\#1b1b18\]{background-color:#1b1b18}.bg-\[\#FDFDFC\]{background-color:#fdfdfc}.bg-\[\#dbdbd7\]{background-color:#dbdbd7}.bg-\[\#fff2f2\]{background-color:#fff2f2}.bg-white{background-color:var(--color-white)}.p-6{padding:calc(var(--spacing) * 6)}.px-5{padding-inline:calc(var(--spacing) * 5)}.py-1{padding-block:calc(var(--spacing) * 1)}.py-1\.5{padding-block:calc(var(--spacing) * 1.5)}.py-2{padding-block:calc(var(--spacing) * 2)}.pb-12{padding-bottom:calc(var(--spacing) * 12)}.text-sm{font-size:var(--text-sm);line-height:var(--tw-leading,var(--text-sm--line-height))}.text-\[13px\]{font-size:13px}.leading-\[20px\]{--tw-leading:20px;line-height:20px}.leading-normal{--tw-leading:var(--leading-normal);line-height:var(--leading-normal)}.font-medium{--tw-font-weight:var(--font-weight-medium);font-weight:var(--font-weight-medium)}.text-\[\#1B1B18\],.text-\[\#1b1b18\]{color:#1b1b18}.text-\[\#706f6c\]{color:#706f6c}.text-\[\#F3BEC7\]{color:#f3bec7}.text-\[\#F8B803\]{color:#f8b803}.text-\[\#F53003\],.text-\[\#f53003\]{color:#f53003}.text-white{color:var(--color-white)}.underline{text-decoration-line:underline}.underline-offset-4{text-underline-offset:4px}.opacity-100{opacity:1}.mix-blend-color{mix-blend-mode:color}.mix-blend-darken{mix-blend-mode:darken}.mix-blend-hard-light{mix-blend-mode:hard-light}.mix-blend-multiply{mix-blend-mode:multiply}.shadow-\[0px_0px_1px_0px_rgba\(0\,0\,0\,0\.03\)\,0px_1px_2px_0px_rgba\(0\,0\,0\,0\.06\)\]{--tw-shadow:0px 0px 1px 0px var(--tw-shadow-color,#00000008), 0px 1px 2px 0px var(--tw-shadow-color,#0000000f);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.shadow-\[inset_0px_0px_0px_1px_rgba\(26\,26\,0\,0\.16\)\]{--tw-shadow:inset 0px 0px 0px 1px var(--tw-shadow-color,#1a1a0029);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.filter{filter:var(--tw-blur,) var(--tw-brightness,) var(--tw-contrast,) var(--tw-grayscale,) var(--tw-hue-rotate,) var(--tw-invert,) var(--tw-saturate,) var(--tw-sepia,) var(--tw-drop-shadow,)}.transition-all{transition-property:all;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-opacity{transition-property:opacity;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.delay-300{transition-delay:.3s}.delay-400{transition-delay:.4s}.duration-750{--tw-duration:.75s;transition-duration:.75s}.\[--stroke-color\:\#1B1B18\]{--stroke-color:#1b1b18}.not-has-\[nav\]\:hidden:not(:has(:is(nav))){display:none}.before\:absolute:before{content:var(--tw-content);position:absolute}.before\:start-\[0\.4rem\]:before{content:var(--tw-content);inset-inline-start:.4rem}.before\:top-0:before{content:var(--tw-content);top:calc(var(--spacing) * 0)}.before\:top-1\/2:before{content:var(--tw-content);top:50%}.before\:bottom-0:before{content:var(--tw-content);bottom:calc(var(--spacing) * 0)}.before\:bottom-1\/2:before{content:var(--tw-content);bottom:50%}.before\:left-\[0\.4rem\]:before{content:var(--tw-content);left:.4rem}.before\:border-l:before{content:var(--tw-content);border-left-style:var(--tw-border-style);border-left-width:1px}.before\:border-\[\#e3e3e0\]:before{content:var(--tw-content);border-color:#e3e3e0}@media(hover:hover){.hover\:border-\[\#1915014a\]:hover{border-color:#1915014a}.hover\:border-\[\#19140035\]:hover{border-color:#19140035}.hover\:border-black:hover{border-color:var(--color-black)}.hover\:bg-black:hover{background-color:var(--color-black)}}@media(min-width:64rem){.lg\:mb-0{margin-bottom:calc(var(--spacing) * 0)}.lg\:mb-6{margin-bottom:calc(var(--spacing) * 6)}.lg\:-ml-px{margin-left:-1px}.lg\:ml-0{margin-left:calc(var(--spacing) * 0)}.lg\:block{display:block}.lg\:aspect-auto{aspect-ratio:auto}.lg\:w-\[438px\]{width:438px}.lg\:max-w-4xl{max-width:var(--container-4xl)}.lg\:grow{flex-grow:1}.lg\:flex-row{flex-direction:row}.lg\:justify-center{justify-content:center}.lg\:rounded-ss-lg{border-start-start-radius:var(--radius-lg)}.lg\:rounded-ee-none{border-end-end-radius:0}.lg\:rounded-t-none{border-top-left-radius:0;border-top-right-radius:0}.lg\:rounded-r-lg{border-top-right-radius:var(--radius-lg);border-bottom-right-radius:var(--radius-lg)}.lg\:p-8{padding:calc(var(--spacing) * 8)}.lg\:p-20{padding:calc(var(--spacing) * 20)}}@media(prefers-color-scheme:dark){.dark\:border-\[\#3E3E3A\]{border-color:#3e3e3a}.dark\:border-\[\#eeeeec\]{border-color:#eeeeec}.dark\:bg-\[\#0a0a0a\]{background-color:#0a0a0a}.dark\:bg-\[\#1D0002\]{background-color:#1d0002}.dark\:bg-\[\#3E3E3A\]{background-color:#3e3e3a}.dark\:bg-\[\#161615\]{background-color:#161615}.dark\:bg-\[\#eeeeec\]{background-color:#eeeeec}.dark\:text-\[\#1C1C1A\]{color:#1c1c1a}.dark\:text-\[\#4B0600\]{color:#4b0600}.dark\:text-\[\#391800\]{color:#391800}.dark\:text-\[\#733000\]{color:#733000}.dark\:text-\[\#A1A09A\]{color:#a1a09a}.dark\:text-\[\#EDEDEC\]{color:#ededec}.dark\:text-\[\#F61500\]{color:#f61500}.dark\:text-\[\#FF4433\]{color:#f43}.dark\:text-black{color:var(--color-black)}.dark\:mix-blend-hard-light{mix-blend-mode:hard-light}.dark\:mix-blend-normal{mix-blend-mode:normal}.dark\:shadow-\[inset_0px_0px_0px_1px_\#fffaed2d\]{--tw-shadow:inset 0px 0px 0px 1px var(--tw-shadow-color,#fffaed2d);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.dark\:\[--stroke-color\:\#FF750F\]{--stroke-color:#ff750f}.dark\:before\:border-\[\#3E3E3A\]:before{content:var(--tw-content);border-color:#3e3e3a}@media(hover:hover){.dark\:hover\:border-\[\#3E3E3A\]:hover{border-color:#3e3e3a}.dark\:hover\:border-\[\#62605b\]:hover{border-color:#62605b}.dark\:hover\:border-white:hover{border-color:var(--color-white)}.dark\:hover\:bg-white:hover{background-color:var(--color-white)}}}@starting-style{.starting\:opacity-0{opacity:0}}@media(prefers-reduced-motion:no-preference){@starting-style{.motion-safe\:starting\:-translate-x-\[26px\]{--tw-translate-x: -26px ;translate:var(--tw-translate-x) var(--tw-translate-y)}}@starting-style{.motion-safe\:starting\:-translate-x-\[51px\]{--tw-translate-x: -51px ;translate:var(--tw-translate-x) var(--tw-translate-y)}}@starting-style{.motion-safe\:starting\:-translate-x-\[78px\]{--tw-translate-x: -78px ;translate:var(--tw-translate-x) var(--tw-translate-y)}}@starting-style{.motion-safe\:starting\:-translate-x-\[102px\]{--tw-translate-x: -102px ;translate:var(--tw-translate-x) var(--tw-translate-y)}}@starting-style{.motion-safe\:starting\:translate-y-6{--tw-translate-y:calc(var(--spacing) * 6);translate:var(--tw-translate-x) var(--tw-translate-y)}}}}@property --tw-translate-x{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-y{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-z{syntax:"*";inherits:false;initial-value:0}@property --tw-rotate-x{syntax:"*";inherits:false}@property --tw-rotate-y{syntax:"*";inherits:false}@property --tw-rotate-z{syntax:"*";inherits:false}@property --tw-skew-x{syntax:"*";inherits:false}@property --tw-skew-y{syntax:"*";inherits:false}@property --tw-space-x-reverse{syntax:"*";inherits:false;initial-value:0}@property --tw-border-style{syntax:"*";inherits:false;initial-value:solid}@property --tw-leading{syntax:"*";inherits:false}@property --tw-font-weight{syntax:"*";inherits:false}@property --tw-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-shadow-color{syntax:"*";inherits:false}@property --tw-shadow-alpha{syntax:"<percentage>";inherits:false;initial-value:100%}@property --tw-inset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-shadow-color{syntax:"*";inherits:false}@property --tw-inset-shadow-alpha{syntax:"<percentage>";inherits:false;initial-value:100%}@property --tw-ring-color{syntax:"*";inherits:false}@property --tw-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-ring-color{syntax:"*";inherits:false}@property --tw-inset-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-ring-inset{syntax:"*";inherits:false}@property --tw-ring-offset-width{syntax:"<length>";inherits:false;initial-value:0}@property --tw-ring-offset-color{syntax:"*";inherits:false;initial-value:#fff}@property --tw-ring-offset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-blur{syntax:"*";inherits:false}@property --tw-brightness{syntax:"*";inherits:false}@property --tw-contrast{syntax:"*";inherits:false}@property --tw-grayscale{syntax:"*";inherits:false}@property --tw-hue-rotate{syntax:"*";inherits:false}@property --tw-invert{syntax:"*";inherits:false}@property --tw-opacity{syntax:"*";inherits:false}@property --tw-saturate{syntax:"*";inherits:false}@property --tw-sepia{syntax:"*";inherits:false}@property --tw-drop-shadow{syntax:"*";inherits:false}@property --tw-drop-shadow-color{syntax:"*";inherits:false}@property --tw-drop-shadow-alpha{syntax:"<percentage>";inherits:false;initial-value:100%}@property --tw-drop-shadow-size{syntax:"*";inherits:false}@property --tw-duration{syntax:"*";inherits:false}@property --tw-content{syntax:"*";inherits:false;initial-value:""}@keyframes spin{to{transform:rotate(360deg)}}@keyframes ping{75%,to{opacity:0;transform:scale(2)}}@keyframes pulse{50%{opacity:.5}}@keyframes bounce{0%,to{animation-timing-function:cubic-bezier(.8,0,1,1);transform:translateY(-25%)}50%{animation-timing-function:cubic-bezier(0,0,.2,1);transform:none}}
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-white">

    {{-- 1. NAVBAR --}}
    <header
        x-data="{
            scrolled: false,
            mobileOpen: false,
            init() {
                this.scrolled = window.scrollY > 10;
                window.addEventListener('scroll', () => { this.scrolled = window.scrollY > 10; }, { passive: true });
            }
        }"
        x-effect="mobileOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''"
        :class="scrolled ? 'border-gray-200 bg-white/95 shadow-sm backdrop-blur-lg' : 'border-transparent'"
        class="sticky top-0 z-50 w-full border-b transition-all duration-300"
    >
        <nav class="mx-auto flex h-14 w-full max-w-5xl items-center justify-between px-4">
            {{-- Left: Logo + Desktop Nav --}}
            <div class="flex items-center gap-4">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex flex-col rounded-md px-2 py-1 leading-none hover:bg-black/5 transition-colors">
                    <span class="text-[10px] font-extrabold tracking-widest text-[#0089CB]">BICOL UNIVERSITY</span>
                    <span class="text-[9px] font-medium tracking-widest text-gray-500">SERVICE REQUEST SYSTEM</span>
                </a>

                {{-- Desktop: Offices Dropdown --}}
                <div class="relative hidden md:block" x-data="{ open: false }" @click.away="open = false">
                    <button
                        @click="open = !open"
                        :class="open ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
                        class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                    >
                        Offices
                        <svg
                            :class="open ? 'rotate-180' : ''"
                            class="size-3.5 transition-transform duration-200"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 top-full z-50 mt-1.5 w-72 origin-top-left rounded-md border border-gray-200 bg-white shadow-lg"
                    >
                        <ul class="space-y-px p-1.5">
                            @foreach ($offices as $office)
                            <li>
                                <a
                                    href="{{ route('offices.show', $office->slug) }}"
                                    class="flex items-center gap-3 rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50 hover:text-gray-900"
                                >
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">{{ $office->name }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        <div class="border-t border-gray-100 px-4 py-2.5">
                            <a href="{{ route('offices.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0089CB] hover:underline">
                                View all offices
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Auth buttons (desktop) --}}
            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('auth.google') }}" class="rounded-md bg-[#0089CB] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#0077b3]">
                    Sign In with Google
                </a>
            </div>

            {{-- Mobile: Hamburger button --}}
            <button
                @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen.toString()"
                aria-controls="mobile-menu"
                aria-label="Toggle menu"
                class="rounded-md border border-gray-200 p-2 text-gray-600 transition-colors hover:bg-gray-50 md:hidden"
            >
                <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </nav>

        {{-- Mobile Menu Overlay --}}
        <div
            id="mobile-menu"
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;"
            class="fixed inset-0 top-14 z-40 flex flex-col overflow-y-auto border-t border-gray-200 bg-white/95 backdrop-blur-lg md:hidden"
        >
            <div class="flex min-h-full flex-col justify-between gap-4 p-4">
                <div class="flex flex-col gap-1">
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Offices</p>
                    @foreach ($offices as $office)
                    <a
                        href="{{ route('offices.show', $office->slug) }}"
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50"
                    >
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium">{{ $office->name }}</span>
                    </a>
                    @endforeach
                    <a href="{{ route('offices.index') }}" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold text-[#0089CB] hover:underline">
                        View all offices
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>

                <div class="flex flex-col gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('login') }}" class="w-full rounded-md border border-gray-300 bg-transparent px-4 py-2.5 text-center text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                        Log In
                    </a>
                    <a href="{{ route('auth.google') }}" class="w-full rounded-md bg-[#0089CB] px-4 py-2.5 text-center text-sm font-medium text-white transition-colors hover:bg-[#0077b3]">
                        Sign In with Google
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- 2. HERO SECTION --}}
    <style>
    @keyframes busrs-fade-up {
        from { opacity: 0; transform: translateY(16px); filter: blur(8px); }
        to   { opacity: 1; transform: translateY(0);    filter: blur(0);   }
    }
    .busrs-anim      { animation: busrs-fade-up 0.8s ease forwards; opacity: 0; }
    .busrs-anim-d1   { animation-delay: 0.05s; }
    .busrs-anim-d2   { animation-delay: 0.2s;  }
    .busrs-anim-d3   { animation-delay: 0.4s;  }
    .busrs-anim-d4   { animation-delay: 0.6s;  }
    .busrs-anim-d5   { animation-delay: 0.85s; }
    </style>
    <section class="relative overflow-hidden pb-0 pt-24 md:pt-32">
        {{-- Radial gradient: transparent at top, white at bottom --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 size-full" style="background:radial-gradient(125% 125% at 50% 100%,transparent 0%,white 75%)"></div>

        {{-- Decorative blue blobs (desktop only) --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-20 hidden overflow-hidden lg:block">
            <div class="absolute left-0 top-0 rounded-full" style="width:35rem;height:70rem;transform:translateY(-300px) rotate(-45deg);background:radial-gradient(68.54% 68.72% at 55.02% 31.46%,rgba(0,137,203,.07) 0,rgba(0,137,203,.01) 50%,transparent 80%)"></div>
            <div class="absolute left-0 top-0 rounded-full" style="width:14rem;height:70rem;transform:translate(5%,-45%) rotate(-45deg);background:radial-gradient(50% 50% at 50% 50%,rgba(0,137,203,.04) 0,transparent 80%)"></div>
        </div>

        {{-- Centered content --}}
        <div class="mx-auto max-w-4xl px-6 text-center">
            {{-- Pill badge --}}
            <div class="busrs-anim busrs-anim-d1 mb-8 flex justify-center">
                <a href="{{ route('login') }}" class="group inline-flex items-center gap-3 rounded-full border border-gray-200 bg-gray-50 px-4 py-1.5 shadow-sm transition-all duration-300 hover:bg-white hover:shadow-md">
                    <span class="text-sm text-gray-600">Now serving 10+ departments at Bicol University</span>
                    <span class="h-4 w-px shrink-0 bg-gray-300"></span>
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white shadow-sm transition-all duration-300 group-hover:bg-[#0089CB]">
                        <svg class="size-3 text-gray-500 transition-colors group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                </a>
            </div>

            {{-- Headline --}}
            <h1 class="busrs-anim busrs-anim-d2 text-balance text-5xl font-black tracking-tight text-[#111111] md:text-6xl lg:text-[5rem]">
                Get the help<br>you need — fast.
            </h1>

            {{-- Subtitle --}}
            <p class="busrs-anim busrs-anim-d3 mx-auto mt-6 max-w-xl text-balance text-lg text-gray-500">
                Submit service requests to any Bicol University department online. No queues. No paperwork. Just results.
            </p>

            {{-- CTAs --}}
            <div class="busrs-anim busrs-anim-d4 mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <div class="flex flex-col items-center gap-1.5">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#0089CB]">New Request</span>
                    <div class="rounded-[14px] border border-gray-200/80 bg-black/5 p-0.5">
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-[#0089CB] px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-[#0077b3]">
                            Submit Now
                        </a>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-1.5">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">Track a Ticket</span>
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-6 py-[11px] text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
                        Track Now
                    </a>
                </div>
            </div>
        </div>

        {{-- Portal preview mockup --}}
        <div class="busrs-anim busrs-anim-d5 relative mt-16 overflow-hidden px-4 sm:mt-20 md:mt-24">
            {{-- Fade-to-white gradient overlay --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-10" style="background:linear-gradient(to bottom,transparent 40%,white 100%)"></div>
            <div class="mx-auto max-w-5xl">
                <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-2xl ring-1 ring-gray-100">
                    {{-- Browser chrome bar --}}
                    <div class="flex items-center justify-between border-b border-zinc-800 bg-zinc-900 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5">
                                <div class="h-2.5 w-2.5 rounded-full bg-red-400/80"></div>
                                <div class="h-2.5 w-2.5 rounded-full bg-yellow-400/80"></div>
                                <div class="h-2.5 w-2.5 rounded-full bg-green-400/80"></div>
                            </div>
                            <span class="text-sm font-bold tracking-tight text-white">BUSRS</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-zinc-400">My Requests</span>
                            <span class="rounded-lg bg-[#0089CB] px-3 py-1 text-xs font-semibold text-white">+ New Request</span>
                        </div>
                    </div>
                    {{-- Ticket list --}}
                    <div class="bg-zinc-950 p-4 sm:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-widest text-zinc-400">My Tickets</span>
                            <span class="text-xs text-zinc-500">3 open</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-900 px-4 py-3">
                                <span class="shrink-0 rounded-md bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-400">Open</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-100">WiFi access issue in Engineering Building</p>
                                    <p class="text-xs text-zinc-500">IT Office · 2 hours ago</p>
                                </div>
                                <span class="hidden shrink-0 font-mono text-xs text-zinc-500 sm:block">BU-2026-001</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-900 px-4 py-3">
                                <span class="shrink-0 rounded-md bg-blue-500/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-400">In Progress</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-100">Request for certified true copy of TOR</p>
                                    <p class="text-xs text-zinc-500">Registrar · 1 day ago</p>
                                </div>
                                <span class="hidden shrink-0 font-mono text-xs text-zinc-500 sm:block">BU-2026-002</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-lg border border-zinc-800 bg-zinc-900 px-4 py-3">
                                <span class="shrink-0 rounded-md bg-green-500/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-green-400">Resolved</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-400">Broken air conditioning unit, Room 301</p>
                                    <p class="text-xs text-zinc-600">Physical Plant · 3 days ago</p>
                                </div>
                                <span class="hidden shrink-0 font-mono text-xs text-zinc-600 sm:block">BU-2026-003</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-lg border border-zinc-800/50 bg-zinc-900/50 px-4 py-3 opacity-60">
                                <span class="shrink-0 rounded-md bg-zinc-700/50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500">Resolved</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-500">Lost ID replacement request</p>
                                    <p class="text-xs text-zinc-600">Student Affairs · 1 week ago</p>
                                </div>
                                <span class="hidden shrink-0 font-mono text-xs text-zinc-600 sm:block">BU-2026-004</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="max-w-5xl mx-auto">

        {{-- 4. STAT LINE --}}
        <div class="bg-[#F5FBFF] border-b border-[#E0F0FA] px-6 lg:px-10 py-3 flex flex-wrap items-center gap-3">
            <span class="text-xs text-[#555555]">Serving <strong class="text-[#111111] font-bold">10+ departments</strong> across Bicol University</span>
            <span class="w-1 h-1 rounded-full bg-gray-300 inline-block shrink-0"></span>
            <span class="text-xs text-[#555555]"><strong class="text-[#111111] font-bold">500+</strong> requests resolved</span>
            <span class="w-1 h-1 rounded-full bg-gray-300 inline-block shrink-0"></span>
            <span class="text-xs text-[#555555]">Avg. response: <strong class="text-[#111111] font-bold">24 hrs</strong></span>
        </div>

        {{-- 5. HOW IT WORKS --}}
        <div class="border-b border-gray-200 px-6 py-20 text-center lg:px-10">
            <span class="inline-flex items-center rounded-full bg-[#0089CB] px-2.5 py-0.5 text-xs font-semibold text-white">
                How It Works
            </span>
            <h2 class="mt-4 text-4xl font-semibold text-[#111111]">Submit. Track. Resolved.</h2>
            <p class="mt-6 font-medium text-gray-500">
                Getting help from any Bicol University department is simple — just three steps.
            </p>
            <div class="mx-auto mt-14 max-w-lg text-left">
                <div class="mb-8 flex gap-4">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-sm bg-gray-100 font-mono text-xs font-semibold text-[#0089CB]">1</span>
                    <div>
                        <h3 class="font-medium text-[#111111]">Submit your request online</h3>
                        <p class="mt-1 text-sm text-gray-500">Fill out a short form describing your concern. Select the department and service type. No sign-up required for basic requests.</p>
                    </div>
                </div>
                <div class="mb-8 flex gap-4">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-sm bg-gray-100 font-mono text-xs font-semibold text-[#0089CB]">2</span>
                    <div>
                        <h3 class="font-medium text-[#111111]">Your ticket is routed automatically</h3>
                        <p class="mt-1 text-sm text-gray-500">The system forwards your request to the right department immediately. You receive a reference number to track progress.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-sm bg-[#0089CB] font-mono text-xs font-semibold text-white">3</span>
                    <div>
                        <h3 class="font-medium text-[#111111]">Get resolved and notified</h3>
                        <p class="mt-1 text-sm text-gray-500">The assigned staff handles your request and updates the status. You are notified once your concern is resolved.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. FEATURE SHOWCASE --}}
        <section class="py-20" aria-labelledby="features-heading">
            <div class="mx-auto flex max-w-2xl flex-col gap-4 text-center">
                <h2 id="features-heading" class="text-4xl font-semibold text-[#111111] md:text-5xl">
                    Built for every student at Bicol University
                </h2>
                <p class="text-gray-500">
                    From filing a request to getting it resolved — BUSRS handles the entire service lifecycle so offices and students never miss a step.
                </p>
            </div>

            <div class="mt-20 flex flex-col">

                {{-- Feature 1: Guided Submission --}}
                <div class="grid items-center gap-12 border-b border-gray-200 pb-16 mb-16 lg:grid-cols-3 xl:gap-20">
                    <div class="flex flex-col gap-8 text-left sm:flex-row lg:col-span-2 lg:border-r lg:pr-12 border-gray-200">
                        <div class="aspect-[29/35] w-full max-w-[200px] shrink-0 overflow-hidden rounded-2xl bg-zinc-900 ring-1 ring-zinc-700 transition-all duration-300 hover:scale-105 p-4 flex flex-col">
                            <div class="flex items-center gap-1 mb-4">
                                <div class="h-1 flex-1 rounded-full bg-[#0089CB]"></div>
                                <div class="h-1 flex-1 rounded-full bg-[#0089CB]"></div>
                                <div class="h-1 flex-1 rounded-full bg-zinc-700"></div>
                                <div class="h-1 flex-1 rounded-full bg-zinc-700"></div>
                                <div class="h-1 flex-1 rounded-full bg-zinc-700"></div>
                            </div>
                            <p class="text-[9px] text-zinc-500 mb-0.5">STEP 2 OF 5</p>
                            <p class="text-xs font-semibold text-white mb-3">Select a Service</p>
                            <div class="mb-1.5 flex items-center gap-2 rounded-md border border-zinc-700 bg-zinc-800/50 px-2.5 py-2">
                                <div class="h-3 w-3 rounded-full border border-zinc-600"></div>
                                <span class="text-[9px] text-zinc-400">Document Request</span>
                            </div>
                            <div class="mb-1.5 flex items-center gap-2 rounded-md border border-[#0089CB] bg-[#0089CB]/10 px-2.5 py-2">
                                <div class="h-3 w-3 rounded-full bg-[#0089CB]"></div>
                                <span class="text-[9px] text-zinc-200">Certificate of Registration</span>
                            </div>
                            <div class="mb-1.5 flex items-center gap-2 rounded-md border border-zinc-700 bg-zinc-800/50 px-2.5 py-2">
                                <div class="h-3 w-3 rounded-full border border-zinc-600"></div>
                                <span class="text-[9px] text-zinc-400">Transcript of Records</span>
                            </div>
                            <div class="mt-auto pt-4">
                                <div class="w-full rounded-md bg-[#0089CB] py-1.5 text-center text-[9px] font-semibold text-white">Continue →</div>
                            </div>
                        </div>
                        <figure class="flex flex-col justify-between gap-6 text-left">
                            <blockquote>
                                <h3 class="text-lg font-normal leading-relaxed text-gray-900 sm:text-xl">
                                    Guided 5-Step Submission Wizard
                                    <span class="mt-2 block text-base leading-relaxed text-gray-500">
                                        Students follow a structured wizard — select an office, choose a service category, pick a service type, complete dynamic custom fields, then review before submitting. File attachments (PDF, JPG, PNG up to 10 MB) are supported at every step.
                                    </span>
                                </h3>
                            </blockquote>
                            <figcaption class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0089CB]">
                                    <svg class="size-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Student Portal</p>
                                    <p class="text-xs text-gray-500">Multi-step request submission</p>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                    <div class="grid grid-cols-1 gap-6 self-center">
                        <div class="flex flex-col gap-2 p-6"
                             x-data="{ displayed: 0 }"
                             x-init="
                                 let obs = new IntersectionObserver(([e]) => {
                                     if (!e.isIntersecting) return;
                                     let n = 0;
                                     let t = setInterval(() => { displayed = ++n; if (n >= 5) clearInterval(t); }, 100);
                                     obs.disconnect();
                                 }, { threshold: 0.4 });
                                 obs.observe($el);
                             ">
                            <p class="text-4xl font-medium text-gray-900"><span x-text="displayed">0</span>-step</p>
                            <p class="font-medium text-gray-900">Guided Wizard</p>
                            <p class="text-gray-600">Office → Category → Service → Fields → Submit</p>
                        </div>
                        <div class="flex flex-col gap-2 p-6">
                            <p class="text-4xl font-medium text-gray-900">10 MB</p>
                            <p class="font-medium text-gray-900">File Attachments</p>
                            <p class="text-gray-600">PDF, JPG, and PNG uploads per request</p>
                        </div>
                    </div>
                </div>

                {{-- Feature 2: Real-Time Tracking (reversed) --}}
                <div class="grid items-center gap-12 border-b border-gray-200 pb-16 mb-16 lg:grid-cols-3 xl:gap-20">
                    <div class="grid grid-cols-1 gap-6 self-center lg:order-1">
                        <div class="flex flex-col gap-2 p-6"
                             x-data="{ displayed: 0 }"
                             x-init="
                                 let obs = new IntersectionObserver(([e]) => {
                                     if (!e.isIntersecting) return;
                                     let n = 0;
                                     let t = setInterval(() => { displayed = ++n; if (n >= 8) clearInterval(t); }, 90);
                                     obs.disconnect();
                                 }, { threshold: 0.4 });
                                 obs.observe($el);
                             ">
                            <p class="text-4xl font-medium text-gray-900"><span x-text="displayed">0</span> statuses</p>
                            <p class="font-medium text-gray-900">Full Lifecycle Tracking</p>
                            <p class="text-gray-600">Pending → In Progress → Forwarded → Resolved</p>
                        </div>
                        <div class="flex flex-col gap-2 p-6">
                            <p class="text-4xl font-medium text-gray-900">100%</p>
                            <p class="font-medium text-gray-900">Audit Trail</p>
                            <p class="text-gray-600">Every action timestamped and logged per ticket</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-8 text-left sm:flex-row lg:order-2 lg:col-span-2 lg:border-l lg:pl-12 border-gray-200">
                        <div class="aspect-[29/35] w-full max-w-[200px] shrink-0 overflow-hidden rounded-2xl bg-zinc-900 ring-1 ring-zinc-700 transition-all duration-300 hover:scale-105 p-4 flex flex-col">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-zinc-500 mb-3">My Requests</p>
                            <div class="mb-2 rounded-md bg-zinc-800 p-2.5">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="font-mono text-[8px] text-zinc-500">BU-8A1F</span>
                                    <span class="rounded-full bg-blue-400/10 px-1.5 py-0.5 text-[7px] font-semibold text-blue-400">In Progress</span>
                                </div>
                                <div class="h-1 w-full rounded-full bg-zinc-700"><div class="h-1 w-3/5 rounded-full bg-[#0089CB]"></div></div>
                            </div>
                            <div class="mb-2 rounded-md bg-zinc-800 p-2.5">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="font-mono text-[8px] text-zinc-500">BU-7D3C</span>
                                    <span class="rounded-full bg-yellow-400/10 px-1.5 py-0.5 text-[7px] font-semibold text-yellow-400">Pending</span>
                                </div>
                                <div class="h-1 w-full rounded-full bg-zinc-700"><div class="h-1 w-1/5 rounded-full bg-[#0089CB]"></div></div>
                            </div>
                            <div class="mb-2 rounded-md bg-zinc-800 p-2.5">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="font-mono text-[8px] text-zinc-500">BU-4A9D</span>
                                    <span class="rounded-full bg-green-400/10 px-1.5 py-0.5 text-[7px] font-semibold text-green-400">Resolved</span>
                                </div>
                                <div class="h-1 w-full rounded-full bg-zinc-700"><div class="h-1 w-full rounded-full bg-green-400"></div></div>
                            </div>
                            <div class="mt-auto border-t border-zinc-800 pt-3">
                                <p class="mb-1.5 text-[8px] text-zinc-500">Timeline</p>
                                <div class="flex gap-1">
                                    <div class="flex-1 rounded bg-[#0089CB]/20 p-1 text-center text-[7px] text-[#0089CB]">Created</div>
                                    <div class="flex-1 rounded bg-zinc-800 p-1 text-center text-[7px] text-zinc-500">Assigned</div>
                                    <div class="flex-1 rounded bg-zinc-800 p-1 text-center text-[7px] text-zinc-500">Resolved</div>
                                </div>
                            </div>
                        </div>
                        <figure class="flex flex-col justify-between gap-6 text-left">
                            <blockquote>
                                <h3 class="text-lg font-normal leading-relaxed text-gray-900 sm:text-xl">
                                    Real-Time Status Tracking & Messaging
                                    <span class="mt-2 block text-base leading-relaxed text-gray-500">
                                        Students track every ticket across 8 lifecycle statuses. A threaded message panel lets staff and students communicate with internal notes, canned responses, and read receipts — all recorded in a timestamped audit trail.
                                    </span>
                                </h3>
                            </blockquote>
                            <figcaption class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0089CB]">
                                    <svg class="size-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Ticket Tracking</p>
                                    <p class="text-xs text-gray-500">Real-time status & messaging</p>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                </div>

                {{-- Feature 3: Smart Routing --}}
                <div class="grid items-center gap-12 lg:grid-cols-3 xl:gap-20">
                    <div class="flex flex-col gap-8 text-left sm:flex-row lg:col-span-2 lg:border-r lg:pr-12 border-gray-200">
                        <div class="aspect-[29/35] w-full max-w-[200px] shrink-0 overflow-hidden rounded-2xl bg-zinc-900 ring-1 ring-zinc-700 transition-all duration-300 hover:scale-105 p-4 flex flex-col">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-zinc-500 mb-3">Ticket Routing</p>
                            <div class="mb-3">
                                <p class="text-[8px] text-zinc-500 mb-1">PRIORITY</p>
                                <div class="flex flex-wrap gap-1">
                                    <span class="rounded-full bg-red-400/10 px-1.5 py-0.5 text-[7px] font-semibold text-red-400">Urgent</span>
                                    <span class="rounded-full bg-orange-400/10 px-1.5 py-0.5 text-[7px] font-semibold text-orange-400">High</span>
                                    <span class="rounded-full bg-blue-400/10 px-1.5 py-0.5 text-[7px] font-semibold text-blue-400 ring-1 ring-blue-400/30">Normal</span>
                                    <span class="rounded-full bg-zinc-700 px-1.5 py-0.5 text-[7px] font-semibold text-zinc-400">Low</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <p class="text-[8px] text-zinc-500 mb-1">FORWARD TO</p>
                                <div class="flex items-center gap-1.5 rounded-md bg-zinc-800 px-2 py-1.5">
                                    <div class="h-2 w-2 rounded-full bg-[#0089CB]"></div>
                                    <span class="text-[8px] text-zinc-300">Physical Plant Office</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <p class="text-[8px] text-zinc-500 mb-1">CREDIT TYPE</p>
                                <div class="flex gap-1">
                                    <span class="rounded bg-[#0089CB]/20 px-1.5 py-0.5 text-[7px] text-[#0089CB] ring-1 ring-[#0089CB]/30">Accept Credit</span>
                                    <span class="rounded bg-zinc-800 px-1.5 py-0.5 text-[7px] text-zinc-500">Reference Only</span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <p class="text-[8px] text-zinc-500 mb-1">SLA TARGET</p>
                                <div class="flex items-center gap-2">
                                    <div class="h-1 flex-1 rounded-full bg-zinc-700"><div class="h-1 w-2/3 rounded-full bg-[#FE8926]"></div></div>
                                    <span class="text-[8px] text-zinc-400">2d left</span>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <div class="w-full rounded-md bg-[#0089CB] py-1.5 text-center text-[9px] font-semibold text-white">Forward Ticket →</div>
                            </div>
                        </div>
                        <figure class="flex flex-col justify-between gap-6 text-left">
                            <blockquote>
                                <h3 class="text-lg font-normal leading-relaxed text-gray-900 sm:text-xl">
                                    Smart Inter-Office Routing & Prioritization
                                    <span class="mt-2 block text-base leading-relaxed text-gray-500">
                                        Staff forward tickets between offices with Accept Credit or Reference Only attribution. Service types carry configurable SLA day targets, tickets range from Low to Urgent priority, and office-scoped canned responses speed up resolution.
                                    </span>
                                </h3>
                            </blockquote>
                            <figcaption class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#FE8926]">
                                    <svg class="size-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Office Administration</p>
                                    <p class="text-xs text-gray-500">Routing, priority & SLA management</p>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                    <div class="grid grid-cols-1 gap-6 self-center">
                        <div class="flex flex-col gap-2 p-6"
                             x-data="{ displayed: 0 }"
                             x-init="
                                 let obs = new IntersectionObserver(([e]) => {
                                     if (!e.isIntersecting) return;
                                     let n = 0;
                                     let t = setInterval(() => { displayed = ++n; if (n >= 4) clearInterval(t); }, 150);
                                     obs.disconnect();
                                 }, { threshold: 0.4 });
                                 obs.observe($el);
                             ">
                            <p class="text-4xl font-medium text-gray-900"><span x-text="displayed">0</span> levels</p>
                            <p class="font-medium text-gray-900">Priority Tiers</p>
                            <p class="text-gray-600">Low, Normal, High, and Urgent per ticket</p>
                        </div>
                        <div class="flex flex-col gap-2 p-6">
                            <p class="text-4xl font-medium text-gray-900">SLA</p>
                            <p class="font-medium text-gray-900">Day Targets</p>
                            <p class="text-gray-600">Configurable resolution deadlines per service type</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </main>

    {{-- 7. FOOTER --}}
    <footer class="border-t bg-gray-50/60">
        <div class="mx-auto max-w-6xl px-4 lg:px-6">
            {{-- 4-column link grid --}}
            <div class="grid grid-cols-2 gap-8 py-8 md:grid-cols-4">
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">University</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="transition-colors hover:text-gray-900">About Bicol University</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">University Website</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">BU Portal</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Announcements</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Academic Calendar</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Departments</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-gray-900">Information Technology Office</a></li>
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-gray-900">Registrar's Office</a></li>
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-gray-900">Physical Plant Office</a></li>
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-gray-900">Student Affairs Office</a></li>
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-gray-900">Finance Office</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Support</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="mailto:itsupport@bicol-u.edu.ph" class="transition-colors hover:text-gray-900">Technical Support</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Help Center</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Getting Started</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">FAQs</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Report an Issue</a></li>
                    </ul>
                    <div class="mt-3 space-y-0.5">
                        <p class="text-[11px] text-gray-400">itsupport@bicol-u.edu.ph</p>
                        <p class="text-[11px] text-gray-400">(052) 820-0000 loc. 101</p>
                        <p class="text-[11px] text-gray-400">Mon–Fri, 8:00 AM – 5:00 PM</p>
                    </div>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Legal</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('legal.privacy') }}" class="transition-colors hover:text-gray-900">Privacy Policy</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="transition-colors hover:text-gray-900">Terms of Use</a></li>
                        <li><a href="{{ route('legal.cookies') }}" class="transition-colors hover:text-gray-900">Cookie Policy</a></li>
                        <li><a href="{{ route('legal.data-protection') }}" class="transition-colors hover:text-gray-900">Data Protection</a></li>
                        <li><a href="{{ route('legal.transparency') }}" class="transition-colors hover:text-gray-900">Transparency Report</a></li>
                    </ul>
                </div>
            </div>

            <div class="h-px bg-gray-200"></div>

            {{-- Social icons + Coming Soon app buttons --}}
            <div class="flex flex-wrap items-center justify-between gap-4 py-5">
                {{-- Social icons --}}
                <div class="flex items-center gap-2">
                    <a href="#" aria-label="Facebook" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-50 hover:text-gray-700">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" aria-label="X / Twitter" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-50 hover:text-gray-700">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.745l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="#" aria-label="LinkedIn" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-50 hover:text-gray-700">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>

                {{-- Coming Soon app buttons --}}
                <div class="flex gap-3">
                    <div class="flex flex-col items-center gap-1">
                        <button disabled class="inline-flex h-11 cursor-not-allowed items-center gap-2 rounded-md bg-gray-800 px-4 py-2 opacity-40">
                            <svg class="size-5 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M18.546 12.763c.024-1.87 1.004-3.597 2.597-4.576-1.009-1.442-2.64-2.323-4.399-2.378-1.851-.194-3.645 1.107-4.588 1.107-.961 0-2.413-1.088-3.977-1.056C6.122 5.927 4.25 7.068 3.249 8.867c-2.131 3.69-.542 9.114 1.5 12.097 1.022 1.461 2.215 3.092 3.778 3.035 1.529-.063 2.1-.975 3.945-.975 1.828 0 2.364.975 3.958.938 1.64-.027 2.674-1.467 3.66-2.942.734-1.041 1.299-2.191 1.673-3.408-1.949-.824-3.216-2.733-3.217-4.849z"/>
                                <path d="M15.535 3.847C16.429 2.773 16.87 1.393 16.763 0c-1.366.144-2.629.797-3.535 1.829-.895 1.019-1.349 2.351-1.261 3.705 1.385.013 2.7-.609 3.568-1.687z"/>
                            </svg>
                            <div class="flex flex-col items-start pr-1 text-left text-white">
                                <span class="text-[10px] leading-none tracking-tighter">Download on the</span>
                                <span class="text-sm font-bold leading-none">App Store</span>
                            </div>
                        </button>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Coming Soon</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <button disabled class="inline-flex h-11 cursor-not-allowed items-center gap-2 rounded-md bg-gray-800 px-4 py-2 opacity-40">
                            <svg class="size-5 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="m21.762 9.942-17.092-9.564c-.721-.466-1.635-.504-2.393-.099C1.509.7 1.031 1.497 1.031 2.369v19.282c0 .872.477 1.668 1.246 2.079.755.404 1.668.37 2.393-.098l17.092-9.564c.756-.423 1.207-1.192 1.207-2.058s-.451-1.635-1.207-2.068zm-5.746-1.413-2.36 2.36-8.354-8.355 10.714 5.995zM2.604 21.906V2.094l9.941 9.906-9.941 9.906zm2.698-.439 8.355-8.355 2.36 2.36-10.715 5.995zm15.692-8.78-3.552 1.987-2.674-2.674 2.674-2.674 3.552 1.987c.363.203.402.548.402.686s-.039.483-.402.686z"/>
                            </svg>
                            <div class="flex flex-col items-start pr-1 text-left text-white">
                                <span class="text-[10px] font-light leading-none tracking-tighter">GET IT ON</span>
                                <span class="text-sm font-bold leading-none">Google Play</span>
                            </div>
                        </button>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Coming Soon</span>
                    </div>
                </div>
            </div>

            <div class="h-px bg-gray-200"></div>

            <div class="py-4 text-center text-xs text-gray-400">
                <p>© {{ date('Y') }} Bicol University — Service Request System. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
