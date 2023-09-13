/**
 * Global JavaScript constants.
 *
 * All JS variable-constants for this theme are defined in this file.
 *
 * @file   The file defines all theme-based JS constants.
 * @author Davey Jacobson <daveyjake21 [at] geemail [dot] com>
 * @since  1.0.0
 */

import Cookies from 'js-cookie';
import { Helpers } from './helpers';
const { adminUrl } = Helpers;

/**
 * ISO-2 country abbreviation to name.
 *
 * @author Marius <m@marius.in>
 * @see https://gist.github.com/maephisto/9228207
 *
 * @since 1.0.0
 *
 * @type {Object}
 */
const COUNTRIES = {
  AF: 'Afghanistan',
  AX: 'Aland Islands',
  AL: 'Albania',
  DZ: 'Algeria',
  AS: 'American Samoa',
  AD: 'Andorra',
  AO: 'Angola',
  AI: 'Anguilla',
  AQ: 'Antarctica',
  AG: 'Antigua and Barbuda',
  AR: 'Argentina',
  AM: 'Armenia',
  AW: 'Aruba',
  AU: 'Australia',
  AT: 'Austria',
  AZ: 'Azerbaijan',
  BS: 'Bahamas',
  BH: 'Bahrain',
  BD: 'Bangladesh',
  BB: 'Barbados',
  BY: 'Belarus',
  BE: 'Belgium',
  BZ: 'Belize',
  BJ: 'Benin',
  BM: 'Bermuda',
  BT: 'Bhutan',
  BO: 'Bolivia',
  BA: 'Bosnia and Herzegovina',
  BW: 'Botswana',
  BV: 'Bouvet Island',
  BR: 'Brazil',
  IO: 'British Indian Ocean Territory',
  BN: 'Brunei Darussalam',
  BG: 'Bulgaria',
  BF: 'Burkina Faso',
  BI: 'Burundi',
  KH: 'Cambodia',
  CM: 'Cameroon',
  CA: 'Canada',
  CV: 'Cape Verde',
  KY: 'Cayman Islands',
  CF: 'Central African Republic',
  TD: 'Chad',
  CL: 'Chile',
  CN: 'China',
  CX: 'Christmas Island',
  CC: 'Cocos (Keeling) Islands',
  CO: 'Colombia',
  KM: 'Comoros',
  CG: 'Congo',
  CD: 'Congo, Democratic Republic',
  CK: 'Cook Islands',
  CR: 'Costa Rica',
  CI: 'Cote D\'Ivoire',
  HR: 'Croatia',
  CU: 'Cuba',
  CY: 'Cyprus',
  CZ: 'Czech Republic',
  DK: 'Denmark',
  DJ: 'Djibouti',
  DM: 'Dominica',
  DO: 'Dominican Republic',
  EC: 'Ecuador',
  EG: 'Egypt',
  EN: 'England',
  SV: 'El Salvador',
  GQ: 'Equatorial Guinea',
  ER: 'Eritrea',
  EE: 'Estonia',
  ET: 'Ethiopia',
  FK: 'Falkland Islands (Malvinas)',
  FO: 'Faroe Islands',
  FJ: 'Fiji',
  FI: 'Finland',
  FR: 'France',
  GF: 'French Guiana',
  PF: 'French Polynesia',
  TF: 'French Southern Territories',
  GA: 'Gabon',
  GM: 'Gambia',
  GE: 'Georgia',
  DE: 'Germany',
  GH: 'Ghana',
  GI: 'Gibraltar',
  GR: 'Greece',
  GL: 'Greenland',
  GD: 'Grenada',
  GP: 'Guadeloupe',
  GU: 'Guam',
  GT: 'Guatemala',
  GG: 'Guernsey',
  GN: 'Guinea',
  GW: 'Guinea-Bissau',
  GY: 'Guyana',
  HT: 'Haiti',
  HM: 'Heard Island & Mcdonald Islands',
  VA: 'Holy See (Vatican City State)',
  HN: 'Honduras',
  HK: 'Hong Kong',
  HU: 'Hungary',
  IS: 'Iceland',
  IN: 'India',
  ID: 'Indonesia',
  IR: 'Iran, Islamic Republic Of',
  IQ: 'Iraq',
  IE: 'Ireland',
  IM: 'Isle Of Man',
  IL: 'Israel',
  IT: 'Italy',
  JM: 'Jamaica',
  JP: 'Japan',
  JE: 'Jersey',
  JO: 'Jordan',
  KZ: 'Kazakhstan',
  KE: 'Kenya',
  KI: 'Kiribati',
  KR: 'Korea',
  KW: 'Kuwait',
  KG: 'Kyrgyzstan',
  LA: 'Lao People\'s Democratic Republic',
  LV: 'Latvia',
  LB: 'Lebanon',
  LS: 'Lesotho',
  LR: 'Liberia',
  LY: 'Libyan Arab Jamahiriya',
  LI: 'Liechtenstein',
  LT: 'Lithuania',
  LU: 'Luxembourg',
  MO: 'Macao',
  MK: 'Macedonia',
  MG: 'Madagascar',
  MW: 'Malawi',
  MY: 'Malaysia',
  MV: 'Maldives',
  ML: 'Mali',
  MT: 'Malta',
  MH: 'Marshall Islands',
  MQ: 'Martinique',
  MR: 'Mauritania',
  MU: 'Mauritius',
  YT: 'Mayotte',
  MX: 'Mexico',
  FM: 'Micronesia, Federated States Of',
  MD: 'Moldova',
  MC: 'Monaco',
  MN: 'Mongolia',
  ME: 'Montenegro',
  MS: 'Montserrat',
  MA: 'Morocco',
  MZ: 'Mozambique',
  MM: 'Myanmar',
  NA: 'Namibia',
  NR: 'Nauru',
  NP: 'Nepal',
  NL: 'Netherlands',
  AN: 'Netherlands Antilles',
  NC: 'New Caledonia',
  NZ: 'New Zealand',
  NI: 'Nicaragua',
  NE: 'Niger',
  NG: 'Nigeria',
  NU: 'Niue',
  NF: 'Norfolk Island',
  MP: 'Northern Mariana Islands',
  NO: 'Norway',
  OM: 'Oman',
  PK: 'Pakistan',
  PW: 'Palau',
  PS: 'Palestinian Territory, Occupied',
  PA: 'Panama',
  PG: 'Papua New Guinea',
  PY: 'Paraguay',
  PE: 'Peru',
  PH: 'Philippines',
  PN: 'Pitcairn',
  PL: 'Poland',
  PT: 'Portugal',
  PR: 'Puerto Rico',
  QA: 'Qatar',
  RE: 'Reunion',
  RO: 'Romania',
  RU: 'Russian Federation',
  RW: 'Rwanda',
  BL: 'Saint Barthelemy',
  SH: 'Saint Helena',
  KN: 'Saint Kitts and Nevis',
  LC: 'Saint Lucia',
  MF: 'Saint Martin',
  PM: 'Saint Pierre and Miquelon',
  VC: 'Saint Vincent and Grenadines',
  WS: 'Samoa',
  SM: 'San Marino',
  ST: 'Sao Tome and Principe',
  SA: 'Saudi Arabia',
  SF: 'Scotland',
  SN: 'Senegal',
  RS: 'Serbia',
  SC: 'Seychelles',
  SL: 'Sierra Leone',
  SG: 'Singapore',
  SK: 'Slovakia',
  SI: 'Slovenia',
  SB: 'Solomon Islands',
  SO: 'Somalia',
  ZA: 'South Africa',
  GS: 'South Georgia and Sandwich Isl.',
  ES: 'Spain',
  LK: 'Sri Lanka',
  SD: 'Sudan',
  SR: 'Suriname',
  SJ: 'Svalbard and Jan Mayen',
  SZ: 'Swaziland',
  SE: 'Sweden',
  CH: 'Switzerland',
  SY: 'Syrian Arab Republic',
  TW: 'Taiwan',
  TJ: 'Tajikistan',
  TZ: 'Tanzania',
  TH: 'Thailand',
  TL: 'Timor-Leste',
  TG: 'Togo',
  TK: 'Tokelau',
  TO: 'Tonga',
  TT: 'Trinidad And Tobago',
  TN: 'Tunisia',
  TR: 'Turkey',
  TM: 'Turkmenistan',
  TC: 'Turks And Caicos Islands',
  TV: 'Tuvalu',
  UG: 'Uganda',
  UA: 'Ukraine',
  AE: 'United Arab Emirates',
  GB: 'United Kingdom',
  US: 'United States',
  UM: 'United States Outlying Islands',
  UY: 'Uruguay',
  UZ: 'Uzbekistan',
  VU: 'Vanuatu',
  VE: 'Venezuela',
  VN: 'Vietnam',
  VG: 'Virgin Islands, British',
  VI: 'Virgin Islands, U.S.',
  WL: 'Wales',
  WF: 'Wallis and Futuna',
  EH: 'Western Sahara',
  YE: 'Yemen',
  ZM: 'Zambia',
  ZW: 'Zimbabwe'
};

/**
 * DataTables breakpoints.
 *
 * @type {Object[]}
 */
const BREAKPOINTS = [
  { name: 'desktop', width: Infinity },
  { name: 'xxxlarge', width: 1920 },
  { name: 'xxlarge-down', width: 1919 },
  { name: 'xxlarge', width: 1440 },
  { name: 'xlarge-down', width: 1439 },
  { name: 'xlarge', width: 1200 },
  { name: 'large-down', width: 1199 },
  { name: 'large', width: 1024 },
  { name: 'wordpress-down', width: 1023 },
  { name: 'wordpress', width: 783 },
  { name: 'medium-down', width: 782 },
  { name: 'tablet-p', width: 768 },
  { name: 'medium', width: 640 },
  { name: 'mobile-down', width: 639 },
  { name: 'mobile', width: 480 },
  { name: 'small-only', width: 479 },
  { name: 'small', width: 0 }
];

/**
 * Fifteen minutes.
 *
 * @since 1.0.0
 *
 * @type {Date}
 */
const FIFTEEN_MINUTES = new Date( ( new Date().getTime() + 15 ) * 60 * 1000 );

/**
 * DataTables loading records animation.
 *
 * @since 1.0.0
 *
 * @type {HTMLElement}
 */
const DT_LOADING = `<img src="${ adminUrl( 'images/wpspin_light-2x.gif' ) }" width="16" height="16" alt="Loading data..." />`;

/**
 * Internationalization instance.
 *
 * @since 1.0.0
 *
 * @type {Intl}
 */
const INTL = new Intl.DateTimeFormat().resolvedOptions();

/**
 * ISO-8601 date for Moment.js.
 *
 * @since 1.0.0
 *
 * @type {string}
 */
const ISO_DATE = 'YYYY-MM-DD';

/**
 * ISO-8601 time for Moment.js.
 *
 * @since 1.0.0
 *
 * @type {string}
 */
const ISO_TIME = 'hh:mm:ss';

/**
 * User's locale settings.
 *
 * @since 1.0.0
 *
 * @type {string}
 */
const LOCALE = INTL.locale.toLowerCase();

/**
 * User's local timezone.
 *
 * @since 1.0.0
 *
 * @type {string}
 */
const TIMEZONE = INTL.timeZone;

/**
 * US date format for Moment.js.
 *
 * @since 1.0.0
 *
 * @type {string}
 */
const US_DATE = 'MMMM D[,] YYYY';

/**
 * US time format for Moment.js.
 *
 * @since 1.0.0
 *
 * @type {string}
 */
const US_TIME = 'h:mma z';

/**
 * UTC timezone identifier.
 *
 * @since 1.0.0
 *
 * @type {string}
 */
const UTC = 'Etc/UTC';

/**
 * Local storage IIFE.
 *
 * @since 1.0.0
 * @since 1.1.0 Switched to `localStorage` from `sessionStorage`.
 */
( function() {
  Cookies.set( 'rdb', { locale: LOCALE, timezone: TIMEZONE }, { expires: 7 });

  if ( localStorage.getItem( 'locale' ) !== LOCALE ) {
    localStorage.removeItem( 'locale' );
    localStorage.setItem( 'locale', LOCALE );
  }

  if ( localStorage.getItem( 'timezone' ) !== TIMEZONE ) {
    localStorage.removeItem( 'timezone' );
    localStorage.setItem( 'timezone', TIMEZONE );
  }
})();

export { BREAKPOINTS, COUNTRIES, FIFTEEN_MINUTES, DT_LOADING, ISO_DATE, ISO_TIME, LOCALE, TIMEZONE, US_DATE, US_TIME, UTC };
