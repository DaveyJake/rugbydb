<?php
/**
 * World Rugby API: Utility Functions
 *
 * @package World_Rugby
 * @subpackage Utilities
 */
class WR_Utilities {
    /**
     * Alias of `self::$directory`.
     *
     * @access protected
     * @static
     *
     * @var string
     */
    protected static $dir;

    /**
     * Local directory to save files to or retrieve files from.
     *
     * @access protected
     * @static
     *
     * @var string
     */
    protected static $directory;

    /**
     * List of found files from specified `$directory`.
     *
     * @access protected
     * @static
     *
     * @var array
     */
    protected static $files;

    /**
     * Team slug. Accepts 'mens-eagles', 'womens-eagles', 'mens-sevens', 'womens-sevens'.
     *
     * @access protected
     * @static
     *
     * @var string
     */
    protected static $team;

    /**
     * Local directory to retrieve files from.
     *
     * @access protected
     * @static
     *
     * @var string
     */
    protected static $team_dir;

    /**
     * External folder path for parsing.
     *
     * @access protected
     * @static
     *
     * @var string
     */
	protected static $external = '/Users/us00278/Desktop';

	/**
	 * Team slugs.
	 *
	 * @access protected
	 * @static
	 *
	 * @var array
	 */
	public static $team_slugs = array( 'mens-eagles', 'mens-sevens', 'womens-eagles', 'womens-sevens', 'team-usa-men', 'team-usa-women' );

    /**
     * Positions by jersey number.
     *
     * @static
     *
     * @var object
     */
    public static $jersey_term_ids = array(
        '1'  => 7,
        '2'  => 8,
        '3'  => 7,
        '4'  => 9,
        '5'  => 9,
        '6'  => 10,
        '7'  => 10,
        '8'  => 11,
        '9'  => 2,
        '10' => 3,
        '11' => 5,
        '12' => 4,
        '13' => 4,
        '14' => 5,
        '15' => 6,
    );

    /**
     * Positions by abbreviation to term ID.
     *
     * @static
     *
     * @var object
     */
    public static $posabbr_term_ids = array(
        'LP'  => 7,
        'HK'  => 8,
        'TP'  => 7,
        'LL'  => 9,
        'RL'  => 9,
        'SR'  => 9,
        'BF'  => 10,
        'OF'  => 10,
        'N8'  => 11,
        'SH'  => 2,
        'FH'  => 3,
        'LW'  => 5,
        'IC'  => 4,
        'OC'  => 4,
        'CE'  => 4,
        'RW'  => 5,
        'WI'  => 5,
        'FB'  => 6,
        'Bck' => 355,
        'For' => 356,
    );

    /**
     * Positions by name to term ID.
     *
     * @static
     *
     * @var object
     */
    public static $position_term_ids = array(
        'Prop'       => 7,
        'Hooker'     => 8,
        'Lock'       => 9,
        'Flanker'    => 10,
        'Number 8'   => 11,
        'Scrum Half' => 2,
        'Fly Half'   => 3,
        'Wing'       => 5,
        'Center'     => 4,
        'Centre'     => 4,
        'Full Back'  => 6,
        'Back'       => 355,
        'Forward'    => 356,
    );

    /**
     * Current term IDs.
     *
     * @static
     *
     * @var object
     */
    public static $teams = array(
        217 => 'mens-eagles',
        218 => 'womens-eagles',
        219 => 'mens-sevens',
        220 => 'womens-sevens',
        221 => 'team-usa-men',
        222 => 'team-usa-women',
    );

    /**
     * Flipped version of `$team_ids`.
     *
     * @var object
     */
    public static $team_ids = array(
        'mens-eagles'    => 217,
        'womens-eagles'  => 218,
        'mens-sevens'    => 219,
        'womens-sevens'  => 220,
        'team-usa-men'   => 221,
        'team-usa-women' => 222,
    );

    /**
     * Team term IDs -- these change for every site.
     *
     * @static
     *
     * @var array
     */
    public static $team_term_ids = array(
        'mens-eagles'    => 217,
        'womens-eagles'  => 218,
        'mens-sevens'    => 219,
        'womens-sevens'  => 220,
        'team-usa-men'   => 221,
        'team-usa-women' => 222,
    );

    /**
     * Team WR IDs -- these change for every site.
     *
     * @static
     *
     * @var array
     */
    public static $team_wr_ids = array(
        'mens-eagles'   => 51,
        'womens-eagles' => 2583,
        'mens-sevens'   => 2422,
        'womens-sevens' => 3974,
    );

    /**
     * Team slug titles.
     *
     * @var array
     */
    public static $team_titles = array(
        'mens-eagles'    => 'Men\'s Eagles',
        'womens-eagles'  => 'Women\'s Eagles',
        'mens-sevens'    => 'Men\'s Sevens',
        'womens-sevens'  => 'Women\'s Sevens',
        'team-usa-men'   => 'Team USA Men',
        'team-usa-women' => 'Team USA Women',
    );

    /**
     * File directory map by team ID.
     *
     * @access protected
     * @static
     *
     * @var array
     */
    protected static $map = array(
        //'36'   => 'men',
        '51'   => 'mens-eagles',
        '2583' => 'womens-eagles',
        '2422' => 'mens-sevens',
        '3974' => 'womens-sevens',
    );

    /**
     * File directory map by team ID.
     *
     * @access protected
     * @static
     *
     * @var array
     */
    protected static $players_map = array(
        //'36'   => 'players/men',
        '51'   => 'players/mens-eagles',
        '2583' => 'players/womens-eagles',
        '2422' => 'players/mens-sevens',
        '3974' => 'players/womens-sevens',
    );

    /**
     * Men's Eagles by name to World Rugby ID.
     *
     * @access protected
     * @static
     *
     * @var array
     */
    protected static $wr_mens_eagles = array(
        'AJ MacGinty'                 => 55462,
        'Aaron Davis'                 => 56632,
        'Aaron Freeman'               => 12648,
        'Aaron Satchwell'             => 12486,
        'Adam Russell'                => 31315,
        'Adam Siddall'                => 48917,
        'Al Lakomskis'                => 13054,
        'Al McFarland'                => 55459,
        'Al Williams'                 => 46834,
        'Aladdin Schirmer'            => 46618,
        'Alan Valentine'              => 12841,
        'Alatini Saulala'             => 7535,
        'Albert Knowles'              => 11707,
        'Albert Tuipulotu'            => 21442,
        'Alec Gletzer'                => 43086,
        'Alec Montgomery'             => 7469,
        'Alec Parker'                 => 7505,
        'Alex Magleby'                => 7763,
        'Alex Maughan'                => 58931,
        'Andre Bachelet'              => 7233,
        'Andre Blom'                  => 8116,
        'Andre Liufau'                => 46612,
        'Andrew Durutalo'             => 29397,
        'Andrew Osborne'              => 32917,
        'Andrew Suniula'              => 35391,
        'Andrew Turner'               => 59933,
        'Andy McGarry'                => 12489,
        'Andy Ryland'                 => 24760,
        'Angus MacLellan'             => 43902,
        'Art Ramage'                  => 11697,
        'Art Ward'                    => 13071,
        'Ata Malifa'                  => 36988,
        'Augie Sanborn'               => 11248,
        'Barry Daily'                 => 8290,
        'Barry Waite'                 => 12712,
        'Barry Williams'              => 13069,
        'Bart Furrow'                 => 13104,
        'Ben Cima'                    => 50018,
        'Ben Hough'                   => 8390,
        'Ben Landry'                  => 55585,
        'Ben Pinkelman'               => 54297,
        'Ben Wiedemer'                => 32914,
        'Benjamin Tarr'               => 50197,
        'Benny Erb'                   => 11236,
        'Bill Bernhard'               => 8483,
        'Bill Fraumann'               => 12711,
        'Bill Hayward'                => 13110,
        'Bill King'                   => 11243,
        'Bill LeClerc'                => 33183,
        'Bill Leversee'               => 8295,
        'Bill Shiflet'                => 8384,
        'Blaine Scully'               => 44123,
        'Blake Burdette'              => 31753,
        'Blane Warhurst'              => 8382,
        'Bo Meyersieck'               => 10949,
        'Bob Causey'                  => 8386,
        'Bob Le Clerc'                => 8499,
        'Bob Lockerem'                => 13128,
        'Boyd Morrison'               => 13135,
        'Brad Andrews'                => 13068,
        'Brad Nelson'                 => 13466,
        'Brannan Smoot'               => 13078,
        'Brendan Daly'                => 61675,
        'Brett Thompson'              => 35583,
        'Brian Barnard'               => 30803,
        'Brian Corcoran'              => 13096,
        'Brian Doyle'                 => 34526,
        'Brian Geraghty'              => 13106,
        'Brian Hightower'             => 7354,
        'Brian Howard'                => 39983,
        'Brian Le May'                => 33330,
        'Brian McClenahan'            => 37036,
        'Brian Schoener'              => 26749,
        'Brian Surgener'              => 12529,
        'Brian Swords'                => 13074,
        'Brian Vizard'                => 7625,
        'Britt Howard'                => 8496,
        'Brodie Orth'                 => 57015,
        'Bruce Henderson'             => 13111,
        'Bruce Monroe'                => 13134,
        'Bryce Campbell'              => 58928,
        'Butch Horwath'               => 7360,
        'CD Labounty'                 => 13124,
        'Calvin Whiting'              => 59932,
        'Cam Dolan'                   => 35569,
        'Cameron Falcon'              => 46051,
        'Carl Hansen'                 => 30800,
        'Cayo Nicolau'                => 13083,
        'Cesar Manelli'               => 12843,
        'Chad Erskine'                => 31316,
        'Chad London'                 => 50706,
        'Chad Slaby'                  => 34525,
        'Chance Wenglewski'           => 57421,
        'Charles \'Red\' Meehan'      => 12818,
        'Charles Doe'                 => 12829,
        'Charles Tilden Jr'           => 12815,
        'Charlie Austin'              => 11239,
        'Chester Allen'               => 11238,
        'Chimere Okezie'              => 12504,
        'Chip Curtis'                 => 13093,
        'Chip Howard'                 => 13114,
        'Chris Baumann'               => 55460,
        'Chris Biller'                => 36982,
        'Chris Campbell'              => 13088,
        'Chris Doherty'               => 7294,
        'Chris Lippert'               => 7416,
        'Chris Miller'                => 13082,
        'Chris Momson'                => 11244,
        'Chris Morrow'                => 7472,
        'Chris O\'Brien'              => 8288,
        'Chris Osentowski'            => 21437,
        'Chris Saint'                 => 35580,
        'Chris Schlereth'             => 12657,
        'Chris Williams'              => 10813,
        'Chris Wyles'                 => 32913,
        'Christian Long'              => 13403,
        'Chuck Tunnacliffe'           => 8296,
        'Clarence Culpepper'          => 13094,
        'Cliff Vogl'                  => 7627,
        'Colby \'Babe\' Slater'       => 12816,
        'Colin Hawley'                => 37073,
        'Conrad Hodgson'              => 12491,
        'Cornelius \'Swede\' Righter' => 46826,
        'Cornelius Dirksen'           => 47093,
        'Courtney Mackay'             => 35405,
        'Craig Sweeney'               => 11009,
        'Dan Anderson'                => 12488,
        'Dan Dorsey'                  => 12485,
        'Dan Fernandez'               => '51-DF',
        'Dan Kennedy'                 => 13122,
        'Dan La Prevotte'             => 39984,
        'Dan Lyle'                    => 7429,
        'Dan Payne'                   => 32931,
        'Dan Power'                   => 37072,
        'Daniel Collins'              => 13582,
        'Danny Barrett'               => 49529,
        'Danny Carroll'               => 6679,
        'Danny Wack'                  => 12708,
        'Dave Bateman'                => 10950,
        'Dave Briley'                 => 13090,
        'Dave Dickson'                => 8381,
        'Dave Hodges'                 => 7357,
        'Dave Horton'                 => 8380,
        'Dave Jenkinson'              => 13117,
        'Dave Stephenson'             => 11001,
        'Dave Wallace'                => 12813,
        'Dave Williams'               => 24163,
        'David Ainuu'                 => 65053,
        'David Care'                  => 13087,
        'David Fee'                   => 13053,
        'David Niu'                   => 8120,
        'David Stroble'               => 8119,
        'David Tameilau'              => 35582,
        'Dean Steinbauer'             => 13076,
        'Deion Mikesell'              => 57209,
        'Deke Gard'                   => 11245,
        'Del Chipman'                 => 11000,
        'Demecus Beach'               => 57030,
        'Dennis Gonzalez'             => 10814,
        'Dennis Jablonski'            => 7407,
        'Dennis Murphy'               => 12714,
        'Dennis Shanagher'            => 7542,
        'Derek Asbun'                 => 46504,
        'Devereaux Ferris'            => 65054,
        'Dick Cooke'                  => 11431,
        'Dino Waldren'                => 59248,
        'Don Guest'                   => 12707,
        'Don James'                   => 10815,
        'Don Younger'                 => 12635,
        'Doug Parks'                  => 13147,
        'Doug Rowe'                   => 21439,
        'Doug Straehley'              => 13075,
        'Dougald Gillies'             => 8488,
        'Dudley DeGroot'              => 12839,
        'Dylan Audsley'               => 48660,
        'Dylan Fawsitt'               => 62526,
        'Ed Burlingham'               => 8387,
        'Ed Schram'                   => 13138,
        'Edward Graff'                => 12842,
        'Edward Turkington'           => 46831,
        'Elwyn Hall'                  => 11705,
        'Eric Fry'                    => 28746,
        'Eric Parthmore'              => 13146,
        'Eric Reed'                   => 8124,
        'Eric Swanson'                => 11013,
        'Eric Whitaker'               => 8286,
        'Fifita Mounga'               => 32915,
        'Folau Niua'                  => 44196,
        'Francois Viljoen'            => 21444,
        'Fred Khasigian'              => 11012,
        'Fred Paoli'                  => 7503,
        'Gannon Moore'                => 64986,
        'Gary Brackett'               => 11010,
        'Gary Hein'                   => 7351,
        'Gary Lambert'                => 8383,
        'Gary Wilson'                 => 11433,
        'Gavin De Bartolo'            => 34522,
        'Gearoid McDonald'            => 47102,
        'Gene Vidal'                  => 12806,
        'George Conahey'              => 15901,
        'George Davis'                => 12805,
        'George Dixon'                => 12834,
        'George Fish'                 => 46830,
        'George Sucher'               => 8125,
        'Gerhard Klerck'              => 21432,
        'Gerry McDonald'              => 12646,
        'Glenn Judge'                 => 13118,
        'Graeme Thomson'              => 13465,
        'Graham Downes'               => 12587,
        'Graham Harriman'             => 47096,
        'Grant Wells'                 => 7747,
        'Greg Goodman'                => 13108,
        'Greg Peterson'               => 39462,
        'Greg Schneeweis'             => 11002,
        'Greg Smith'                  => 13407,
        'Guy Voight'                  => 11704,
        'Hal Edwards'                 => 13581,
        'Hanco Germishuys'            => 50016,
        'Harold von Schmidt'          => 12810,
        'Harry Higgins'               => 58430,
        'Hayden Mexted'               => 30828,
        'Hayden Smith'                => 35371,
        'Heaton Wrenn'                => 12814,
        'Henry Bloomfield'            => 33331,
        'Herbert Stolz'               => 11708,
        'Hulu Moungaloa'              => 61671,
        'I Loveseth'                  => 10953,
        'Ian Gunn'                    => 12500,
        'Ian Stevens'                 => 8507,
        'Inaki Basauri'               => 32930,
        'J Finstuen'                  => 20462,
        'JJ Gagiano'                  => 35373,
        'JP Eloff'                    => 57017,
        'Jack Clark'                  => 13085,
        'Jack Glascock'               => 11706,
        'Jacob Sprague'               => 37955,
        'Jacob Waasdorp'              => 15978,
        'Jake Anderson'               => 57018,
        'Jake Burkhardt'              => 13089,
        'Jamason Fa\'anana-Schultz'   => 67454,
        'James Arrell'                => 11250,
        'James Bird'                  => 57016,
        'James Fitzpatrick'           => 46828,
        'James Hilterbrand'           => 47937,
        'James Keller'                => 13120,
        'James King'                  => 57124,
        'James Lik'                   => 26746,
        'James Paterson'              => 28754,
        'James Winston'               => 12812,
        'Jamie Grant'                 => 11432,
        'Jared Hopkins'               => 13113,
        'Jason Gillam'                => 13107,
        'Jason Kelly'                 => 30829,
        'Jason Keyter'                => 7766,
        'Jason Lauritsen'             => 24162,
        'Jason Lett'                  => 34523,
        'Jason Pye'                   => 31062,
        'Jason Raven'                 => 13144,
        'Jason Walker'                => 7632,
        'Jason Wood'                  => 13149,
        'Jay Hanson'                  => 10952,
        'Jay Wilkerson'               => 7635,
        'Jeff Hollings'               => 13112,
        'Jeff Hullinger'              => 30798,
        'Jeff Lombard'                => 13129,
        'Jeff Peter'                  => 7512,
        'Jeff Schraml'                => 13137,
        'Jeremy Nash'                 => 30830,
        'Jerry Kelleher'              => 13119,
        'Jesse Coulson'               => 8121,
        'Jesse Lopez'                 => 13130,
        'Jim Aston'                   => 13794,
        'Joe Burke'                   => 8287,
        'Joe Clarkson'                => 8379,
        'Joe Clayton'                 => 7749,
        'Joe Rissone'                 => 12647,
        'Joe Santos'                  => 13140,
        'Joe Scheitlin'               => 13139,
        'Joe Taufetee'                => 56281,
        'John Buchholz'               => 12482,
        'John Burke'                  => 12636,
        'John Cullen'                 => 49532,
        'John Everett'                => 8391,
        'John Fowler'                 => 11434,
        'John Hartman'                => 10947,
        'John Jellaco'                => 12501,
        'John Knutson'                => 13123,
        'John McBride'                => 8502,
        'John McGeachy'               => 12640,
        'John Mickel'                 => 13133,
        'John Muldoon'                => 12817,
        'John O\'Neil'                => 12819,
        'John Patrick'                => 12807,
        'John Quill'                  => 47101,
        'John Tarpoff'                => 12490,
        'John Van Der Giessen'        => 33175,
        'John Vitale'                 => 31314,
        'Jon Hartman'                 => 27142,
        'Jon Holtzman'                => 8495,
        'Jone Naqica'                 => 12494,
        'Joseph Hunter'               => 46829,
        'Joseph McKim'                => 11249,
        'Joseph Urban'                => 11698,
        'Joseph Welch'                => 35376,
        'Josh Whippy'                 => 61680,
        'Jovesa Naivalu'              => 7743,
        'Juan Grobler'                => 7745,
        'Junior Sifa'                 => 35372,
        'Jurie Gouws'                 => 16175,
        'Justin Boyd'                 => 28733,
        'Justin Stencel'              => 26091,
        'Kain Cross'                  => 15973,
        'Kapeli Pifeleti'             => 64702,
        'Karl Schaupp'                => 11246,
        'Kevin Dalzell'               => 7748,
        'Kevin Higgins'               => 7353,
        'Kevin Swiryn'                => 36986,
        'Kevin Swords'                => 7566,
        'Kimball Kjar'                => 12492,
        'Kingsley McGowan'            => 46057,
        'Kip Oxman'                   => 10999,
        'Kirk Khasigian'              => 7750,
        'Kirk Miles'                  => 12502,
        'Kort Schubert'               => 12487,
        'Kurt Shuman'                 => 7543,
        'Kyle Sumsion'                => 49565,
        'Lance Manga'                 => 8293,
        'Langilangi Haupeakui'        => 58573,
        'Lemoto Filikitonga'          => 48657,
        'Lenny Sanft'                 => 13141,
        'Leonard Peters'              => 39985,
        'Liam Murphy'                 => 32420,
        'Lin Walton'                  => 11429,
        'Link Wilfley'                => 7765,
        'Linn Farrish'                => 12838,
        'Lorenzo Thomas'              => 57085,
        'Louis Cass'                  => 11701,
        'Louis Stanfill'              => 27143,
        'Luke Gross'                  => 7345,
        'Luke Hume'                   => 46503,
        'Madison Hughes'              => 43088,
        'Maika Sika'                  => 7544,
        'Malakai Delai'               => 7746,
        'Malon Al-Jiboori'            => 59977,
        'Marc L\'Huillier'            => 8126,
        'Marcel Brache'               => 49923,
        'Mark Aylor'                  => 30802,
        'Mark Carlson'                => 7265,
        'Mark Crick'                  => 32910,
        'Mark Deaton'                 => 10951,
        'Mark Dunning'                => '51-MD',
        'Mark Gale'                   => 13105,
        'Mark Griffin'                => 15976,
        'Mark Ormsby'                 => 13148,
        'Mark Pidcock'                => 8291,
        'Mark Sawicki'                => 8300,
        'Mark Scharrenberg'           => 7538,
        'Mark Williams'               => 7637,
        'Mark van der Molen'          => 13072,
        'Martin Iosefo'               => 54679,
        'Matai Leuta'                 => 54992,
        'Matekitonga Moeakiola'       => 33329,
        'Matt Alexander'              => 7221,
        'Matt Hawkins'                => 39982,
        'Matt Jensen'                 => 58929,
        'Matt Kane'                   => 7762,
        'Matt Pickston'               => 15900,
        'Matt Sherman'                => 18125,
        'Matthew Trouville'           => 54388,
        'Matthew Wyatt'               => 21438,
        'Michael de Jong'             => 8289,
        'Michelangelo Sosene Feagai'  => 48829,
        'Mick Ording'                 => 11011,
        'Mika Kruse'                  => 64699,
        'Mika McLeod'                 => 7455,
        'Mike Caulder'                => 8375,
        'Mike Fabling'                => 13099,
        'Mike Fanucchi'               => 13098,
        'Mike French'                 => 26748,
        'Mike Garrity'                => 57031,
        'Mike Halliday'               => 12505,
        'Mike Hercus'                 => 12483,
        'Mike Hobson'                 => 24761,
        'Mike Inns'                   => 13115,
        'Mike Liscovitz'              => 13127,
        'Mike MacDonald'              => 12484,
        'Mike Mangan'                 => 26747,
        'Mike Palefau'                => 24763,
        'Mike Petri'                  => 33171,
        'Mike Purcell'                => 8376,
        'Mike Saunders'               => 7536,
        'Mike Sherlock'               => 13081,
        'Mike Siano'                  => 13080,
        'Mike Smith'                  => 13079,
        'Mike Stanaway'               => 13077,
        'Mike Swiderski'              => 11005,
        'Mike Teo'                    => 48658,
        'Mike Waterman'               => 13070,
        'Mile Pulu'                   => 41861,
        'Miles Craigwell'             => 47092,
        'Mone Laulaupealu'            => 34524,
        'Monty Morris'                => 11242,
        'Morris Kirksey'              => 46825,
        'Morris O\'Donnell'           => 12713,
        'Mose Timoteo'                => 7764,
        'Mow Mitchell'                => 11700,
        'Nate Augspurger'             => 51378,
        'Nate Brakeley'               => 57052,
        'Neal Brendel'                => 8388,
        'Nese Malifa'                 => 32912,
        'Nic Johnson'                 => 41907,
        'Nick Boyer'                  => 62424,
        'Nick Civetta'                => 47089,
        'Nick Edwards'                => 39986,
        'Nick Wallace'                => 36710,
        'Niku Kruger'                 => 55461,
        'Norm Mottram'                => 8294,
        'Norman Cleveland'            => 12835,
        'Norman Slater'               => 46832,
        'Olive Kilifi'                => 49572,
        'Olo Fifita'                  => 7758,
        'Otis Purvis'                 => 13533,
        'Owen Lentz'                  => 30799,
        'Paddy Ryan'                  => 61039,
        'Pat Blair'                   => 51385,
        'Pat Bolger'                  => 15899,
        'Pat Danahy'                  => 37071,
        'Pat Johnson'                 => 7372,
        'Pat Malloy'                  => 13131,
        'Pat Quinn'                   => 32911,
        'Pate Tuilevuka'              => 30804,
        'Patrick Bell'                => 30801,
        'Paul Emerick'                => 16006,
        'Paul Lasike'                 => 62527,
        'Paul Mullen'                 => 43096,
        'Paul Sheehy'                 => 8285,
        'Paul Still'                  => 7752,
        'Pete Malcolm'                => 50031,
        'Peter Dahl'                  => 36984,
        'Peter Galicz'                => 27127,
        'Peter Tiberio'               => 35584,
        'Phil Clark'                  => 46833,
        'Phil Thiel'                  => 37954,
        'Philip Eloff'                => 7744,
        'Philip Harrigan'             => 11241,
        'Philippe Farner'             => 7754,
        'Psalm Wooching'              => 61677,
        'Ralph Noble'                 => 11237,
        'Ramon Samaniego'             => 13142,
        'Ray Green'                   => 8489,
        'Ray Lehner'                  => 7408,
        'Ray Nelson'                  => 8284,
        'Riaan Hamilton'              => 12493,
        'Riaan van Zyl'               => 15975,
        'Rich Ederle'                 => 13769,
        'Rich Schurfeld'              => 8117,
        'Richard \'Dick\' Hyland'     => 12832,
        'Richard Liddington'          => 21434,
        'Richard Tardits'             => 7577,
        'Rick Bailey'                 => 8389,
        'Rick Crivellone'             => 13095,
        'Rob Bordley'                 => 11004,
        'Rob Duncanson'               => 13092,
        'Rob Farley'                  => 8298,
        'Rob Lumkong'                 => 7425,
        'Rob Randell'                 => 12649,
        'Robbie Flynn'                => 7760,
        'Robbie Shaw'                 => 30509,
        'Robert \'Dink\' Templeton'   => 46827,
        'Robert Devereaux'            => 12836,
        'Roger Grant'                 => 13109,
        'Roland Blasé'                => 11703,
        'Roland Suniula'              => 36985,
        'Ron Rosser'                  => 31754,
        'Ron Zenker'                  => 7646,
        'Ronald McLean'               => 54608,
        'Rory Lewis'                  => 13126,
        'Rory Mather'                 => 13475,
        'Roy Helu'                    => 8377,
        'Ruben de Haas'               => 57408,
        'Rudy Scholz'                 => 12808,
        'Russ Isaac'                  => 13116,
        'Ryan Chapman'                => 44195,
        'Ryan Fried'                  => 12639,
        'Ryan Matyas'                 => 51524,
        'Salesi Sika'                 => 21441,
        'Samu Manoa'                  => 41859,
        'Scott Bracken'               => 13091,
        'Scott Jones'                 => 24764,
        'Scott Kelso'                 => 13121,
        'Scott LaValla'               => 29323,
        'Scott Lawrence'              => 21433,
        'Scott Peterson'              => 32916,
        'Scott Yungling'              => 7645,
        'Seamus Kelly'                => 48916,
        'Sean Allen'                  => 7223,
        'Seta Tuilevuka'              => 42071,
        'Shalom Suniula'              => 41862,
        'Shaun Davies'                => 46505,
        'Shaun Paga'                  => 8122,
        'Shawn Lipman'                => 13374,
        'Shawn Pittman'               => 32421,
        'Siaosi Mahoni'               => 58930,
        'Skip Niebauer'               => 11008,
        'Sterling Peart'              => 11240,
        'Steve Auerbach'              => 11003,
        'Steve Finkel'                => 8385,
        'Steve Gray'                  => 11428,
        'Steve Hiatt'                 => 8492,
        'Steve Laporta'               => 13125,
        'Steve Tomasin'               => 51604,
        'Tadhg Leader'                => 64697,
        'Tai Enosa'                   => 35570,
        'Tai Tuisamoa'                => 49528,
        'Takudzwa Ngwenya'            => 33169,
        'Taylor Mokate'               => 32419,
        'Terry Scott'                 => 12710,
        'Terry Whelan'                => 12503,
        'Thretton Palamo'             => 33170,
        'Tim Kluempers'               => 7761,
        'Tim Maupin'                  => 49530,
        'Tim Moser'                   => 13136,
        'Tim O\'Brien'                => 10948,
        'Tim Petersen'                => 13145,
        'Tim Stanfill'                => 54390,
        'Tim Usasz'                   => 36987,
        'Titi Lamositele'             => 49434,
        'Toby L\'Estrange'            => 47374,
        'Todd Clever'                 => 23220,
        'Tom Altemeier'               => 13067,
        'Tom Billups'                 => 7244,
        'Tom Bliss'                   => 46047,
        'Tom Coolican'                => 45221,
        'Tom Katzfey'                 => 29322,
        'Tom Kelleher'                => 12638,
        'Tom Klein'                   => 11007,
        'Tom McCormack'               => 13132,
        'Tom Selfridge'               => 11006,
        'Tom Smith'                   => 11430,
        'Tom Vinick'                  => 8378,
        'Tomasi Takau'                => 7574,
        'Tony Flay'                   => 8292,
        'Tony Lamborn'                => 58431,
        'Tony Petruzzella'            => 24191,
        'Tony Purpura'                => 39997,
        'Tony Ridnell'                => 8297,
        'Troy Hall'                   => 41858,
        'Tyson Meek'                  => 26090,
        'Vaea Anitoni'                => 7227,
        'Vaha Esikia'                 => 30797,
        'Vaka'                        => 13073,
        'Vili Toluta\'u'              => 48655,
        'Volney Rouse'                => 39987,
        'Warren Smith'                => 11247,
        'Wayne Chai'                  => 13086,
        'Whit Everett'                => 13100,
        'Will Holder'                 => 46502,
        'Will Hooley'                 => 48697,
        'Will Johnson'                => 36983,
        'Will Magie'                  => 43093,
        'William \'Lefty\' Rogers'    => 12831,
        'William Darsie'              => 11702,
        'Willie Jefferson'            => 7371,
        'Woody Stone'                 => 12709,
        'Zach Fenoglio'               => 35571,
        'Zach Pangelinan'             => 47099,
        'Zack Test'                   => 32424,
    );

    /**
     * Define team constants by ID.
     *
     * @access protected
     * @static
     */
    public static function constants() {
        //define( 'IRELAND', 36 );
        define( 'MENS_EAGLES', 51 );
        define( 'WOMENS_EAGLES', 2583 );
        define( 'MENS_SEVENS', 2422 );
        define( 'WOMENS_SEVENS', 3974 );
    }

    /**
     * Convert player's height from cm to inches.
     *
     * @static
     *
     * @param int $cm Centimeters
     *
     * @return string {$ft}-{$in}
     */
    public static function cm2ft( $cm ) {
        $inches = $cm / 2.54;
        $feet   = intval( $inches / 12 );
        $inches = $inches % 12;

        return sprintf( '%d-%d', $feet, $inches );
    }

    /**
     * Convert player's height from inches to cm.
     *
     * @static
     *
     * @param int $ft Feet.
     * @param int $in Inches.
     *
     * @return string Height in centimeters.
     */
    public static function ft2cm( $ft, $in ) {
        $inches = ( ( $ft * 12 ) + $in );

        return intval( $inches * 2.54 );
    }

    /**
     * Convert player's weight from kilos to pounds.
     *
     * @static
     *
     * @param int $kgs Kilograms.
     *
     * @return int Weight in lbs.
     */
    public static function kg2lb( $kgs ) {
        return intval( $kgs * 2.20462 );
    }

    /**
     * Convert player's weight from pounds to kilos.
     *
     * @static
     *
     * @param int $kgs Kilograms.
     *
     * @return int Weight in lbs.
     */
    public static function lb2kg( $lbs ) {
        return intval( $lbs / 2.20462 );
    }

	/**
     * Convert time to UTC.
     *
     * @static
     *
     * @param string $date_str Date and time string.
     *
     * @return string UTC date and time.
     */
    public static function utc_date_time( $date_str, $format = 'Y-m-d H:i:s' ) {
    	$__date = date( DATE_W3C, strtotime( $date_str ) );
		$_date  = new DateTime( $__date, new DateTimeZone( 'GMT-0' ) );

		return $_date->format( $format );
    }

    /**
     * Convert time to website timezone.
     *
     * @static
     *
     * @param string $date_str Date and time string.
     *
     * @return string Local date and time or NYC as fallback.
     */
    public static function local_date_time( $date_str, $format = 'Y-m-d H:i:s' ) {
    	$__date = date( DATE_W3C, strtotime( $date_str ) );
		$_date  = new DateTime( $__date, new DateTimeZone( 'GMT-0' ) );

		if ( function_exists( 'wp_timezone' ) ) {
			$date = $_date->setTimezone( wp_timezone() );
		} else {
			$date = $_date->setTimezone( new DateTimeZone( 'America/Denver' ) );
		}

		return $date->format( $format );
    }

    /**
     * Get match ID from filename string.
     *
     * @since 1.0.0
     * @access protected
     * @static
     *
     * @param string $file Name of downloaded WR file.
     *
     * @return int|bool    Match ID if found. False if not.
     */
    protected static function extract_match_ID( $file ) {
        return preg_replace( '/\.json/', '', $file );
    }

    /**
     * Get the file contents and parse from JSON.
     *
     * @static
     *
     * @see WR_Data_Organizer::merge_files()
     *
     * @param string $file  The name of the file to parse.
     * @param bool   $array True if you want an associative array. Default is false.
     *
     * @return mixed
     */
    public static function get_file_data( $file, $assoc_array = false ) {
    	if ( file_exists( $file ) ) {
	        return json_decode( file_get_contents( $file ), $assoc_array );
    	}
    }

    /**
     * Target the files inside any directory.
     *
     * @static
     *
     * @see WR_Data_Organizer::merge_files()
     *
     * @param string $directory The name of the directory.
     */
    public static function get_files( $directory ) {
        return array_slice( array_diff( scandir( $directory ), array( '..', '.', '.DS_Store' ) ), 0 );
    }

    /**
     * Get the specified team property.
     *
     * @static
     *
     * @param array  $teams    The current dataset located at `$data->match->teams`.
     * @param string $property Default 'id'. Accepts 'name', 'abbreviation'.
     * @param int    $index    Accepts 0 (home), 1 (away). Default 0.
     *
     * @return int|string The team data property.
     */
    public static function get_team( $teams, $property = 'id', $index = 0 ) {
        return $teams[ $index ]->{$property};
    }

    /**
     * Get any match by its World Rugby ID.
     *
     * @static
     *
     * @param int    $match_id World Rugby match ID.
     * @param string $team     Team slug.
     * @param string $key      Specific data item to return. Accepts 'all', 'match',
     *                         'team', 'timeline', 'officials'.
     * @param bool   $assoc    Convert to array?
     *
     * @return mixed The contents of the match file.
     */
    public static function get_match( $match_id = 0, $team = '', $key = 'all', $assoc = false ) {
        if ( empty( $match_id ) ) {
            return '<pre><em>' . __FUNCTION__ . '</em> Missing Match ID parameter</pre>';
        }

        if ( empty( $team ) ) {
            return '<pre><em>' . __FUNCTION__ . '</em> Missing Team parameter</pre>';
        }

        $files = self::get_files( "./match/{$team}" );

        if ( in_array( "{$match_id}.json", $files, true ) ) {
            $data = self::get_file_data( "./match/{$team}/{$match_id}.json", $assoc );

            if ( is_object( $data ) ) {
                $match     = $data->match;
                $team      = $data->team;
                $timeline  = isset( $data->timeline ) ? $data->timeline : '';
                $officials = isset( $data->officials ) ? $data->officials : '';
            } else {
                $match     = $data['match'];
                $team      = $data['team'];
                $timeline  = isset( $data['timeline'] ) ? $data['timeline'] : '';
                $officials = isset( $data['officials'] ) ? $data['officials'] : '';
            }

            $dataset = array(
                'match'     => $match,
                'team'      => $team,
                'timeline'  => $timeline,
                'officials' => $officials,
            );

            if ( 'all' === $key ) {
                return $dataset;
            } else {
                return $dataset[ $key ];
            }
        } else {
            return 'Match not found';
        }
    }

    /**
     * Get match season from competition.
     *
     * @static
     *
     * @param int    $match_id World Rugby match ID.
     * @param string $team     Team slug.
     *
     * @return string|int   The competition season.
     */
    public static function get_competition_season( $match_id, $team = '' ) {
        $match       = self::get_match( $match_id, $team, 'match' );
        $competition = $match->competition;

        if ( preg_match( '/Olympic/', $competition ) ) {
            preg_match( '/\d{4}/', $match->events[0]->abbr, $matches );
        } else {
            preg_match( '/(\d{4})([^0-9]+)?(\d{2})?/', $competition, $matches );
        }

        if ( empty( $matches ) ) {
            self::debug( "Check match/{$team}/{$match_id}.json - Season not found" );
        } else {
            return $matches;
        }
    }

    /**
     * Get match date.
     *
     * @static
     *
     * @param mixed $match World Rugby match data.
     *
     * @return string   Formatted match date.
     */
    public static function get_match_date( $match ) {
        $match = isset( $match->match ) ? $match->match : $match;

        return array(
            'gmt'  => self::utc_date_time( $match->time->label ),
            'site' => self::local_date_time( $match->time->label ),
        );
    }

    /**
     * Get all match IDs.
     *
     * @static
     *
     * @param string $team Team slug.
     *
     * @return array List of match IDs.
     */
    public static function get_match_ids( $team = '' ) {
        if ( empty( $team ) ) {
            return '<pre><em>' . __FUNCTION__ . '</em> Missing Team parameter</pre>';
        }

        $match_ids = array();

        $files = self::get_files( "./match/{$team}" );

        foreach ( $files as $file ) {
            $file = str_replace( '.json', '', $file );

            $match_ids[] = (int) $file;
        }

        return $match_ids;
    }

    /**
     * Get the match timeline.
     *
     * @static
     *
     * @param int    $match_id World Rugby match ID.
     * @param string $team     Team slug.
     *
     * @return bool|array False if empty timeline or match timeline.
     */
    public static function get_match_players( $match_id = 0, $team = '' ) {
        if ( empty( $team ) ) {
            return '<pre><em>' . __FUNCTION__ . '</em> Missing Team parameter</pre>';
        }

        $data = self::get_match( $match_id, $team );

        return $data['team']->players;
    }

    /**
     * Get the match timeline.
     *
     * @static
     *
     * @param int    $match_id World Rugby match ID.
     * @param string $team     Team slug.
     *
     * @return bool|array False if empty timeline or match timeline.
     */
    public static function get_match_timeline( $match_id = 0, $team = '' ) {
        if ( empty( $team ) ) {
            return '<pre><em>' . __FUNCTION__ . '</em> Missing Team parameter</pre>';
        }

        $data = self::get_match( $match_id, $team );

        return $data['timeline'];
    }

    /**
     * Get the match title.
     *
     * @static
     *
     * @param array $teams The current dataset located at `$data->match->teams`.
     *
     * @return string The pseudo `post_title` from World Rugby -> WPCM.
     */
    public static function get_match_title( $teams ) {
        return $teams[0]->name . ' v ' . $teams[1]->name;
    }

    /**
     * Get the opponent object.
     *
     * @static
     *
     * @see WR_Utilities::get_opponent_index()
     *
     * @param array  $teams    The current dataset located at `$data->match->teams`.
     * @param string $property The name of the key to retrieve. Accepts 'id', 'altId',
     *                         'name', 'abbreviation', 'annotations'. Default ''.
     *
     * @return stdClass {
     *     Opponent object.
     *
     *     @type int    $id           Opponent's World Rugby ID.
     *     @type string $altId        Opponent's World Rugby alternate ID.
     *     @type string $name         Opponent's full name (e.g. 'USA'; 'USA 7s').
     *     @type string $abbreviation Opponent's abbreviated name (ex 'CAN', 'ENG').
     *     @type mixed  $annotations  Default null.
     * }
     */
    public static function get_opponent( array $teams, string $property = '' ) {
        $opp_index = self::get_opponent_index( $teams );
        $opponent  = $teams[ $opp_index ];

        if ( ! empty( $property ) ) {
            return $opponent->{$property};
        }

        return $opponent;
    }

    /**
     * Get the opponent's World Rugby ID.
     *
     * @static
     *
     * @see WR_Utilities::get_opponent()
     *
     * @param array $teams The current dataset located at `$data->match->teams`.
     *
     * @return int
     */
    public static function get_opponent_wr_id( array $teams ) {
        return self::get_opponent( $teams, 'id' );
    }

    /**
     * Get the opponent index from the match (e.g. 0 = home, 1 = away).
     *
     * @static
     *
     * @see WR_Data_Organizer::merge_files()
     * @see WR_Data_Organizer::get_sorted_players()
     *
     * @param array $teams The current dataset located at `$data->match->teams`.
     *
     * @return int
     */
    public static function get_opponent_index( array $teams ) {
        if ( ! ( defined( 'MENS_EAGLES' ) && defined( 'MENS_SEVENS' ) && defined( 'WOMENS_EAGLES' ) && defined( 'WOMENS_SEVENS' ) ) ) {
            self::constants();
        }

        if ( in_array( $teams[0]->id, array( MENS_EAGLES, MENS_SEVENS, WOMENS_EAGLES, WOMENS_SEVENS ) ) ) {
            return (int) 1;
        }
        else {
            return (int) 0;
        }
    }

    /**
     * Get the player by their World Rugby ID.
     *
     * @static
     *
     * @see WR_Utilities::get_files()
     *
     * @param int    $wr_id World Rugby ID.
     * @param string $team  Team slug.
     *
     * @return mixed
     */
    public static function get_player( $wr_id, $team = '' ) {
        if ( empty( $wr_id ) || empty( $team ) ) {
            return 'Error: Missing `$wr_id` or `$team` from `WR_Utilities::get_player` method';
        }

        $dir = __DIR__ . "/players/{$team}/{$wr_id}.json";

        return self::get_file_data( $dir );
    }

    /**
     * Get the player's first name.
     *
     * @static
     *
     * @param string $name Official name from World Rugby.
     *
     * @return string  Player's first name.
     */
    public static function get_player_firstname( $player_name ) {
        if ( preg_match( '/\s/', $player_name ) ) {
            $parts     = preg_split( '/\s/', $player_name );
            $firstname = $parts[0];
        } else {
            $firstname = $player_name;
        }

        return $firstname;
    }

    /**
     * Get the player's nickname.
     *
     * @static
     *
     * @param mixed $player_data Player's WR object.
     *
     * @return string    Player's nickname.
     */
    public static function get_player_nickname( $player_data ) {
        if ( ! is_null( $player_data->player->nickname ) ) {
            return $player_data->player->nickname;
        } elseif ( ! is_null( $player_data->player->name->first->known ) ) {
            return $player_data->player->name->first->known;
        }

        $parts = preg_split( '/\s/', $player_data->player->name->display );

        return $parts[0];
    }

    /**
     * Get the team index from the match (e.g. 0 = home, 1 = away).
     *
     * @static
     *
     * @see WR_Data_Organizer::merge_files()
     * @see WR_Data_Organizer::get_sorted_players()
     *
     * @param array $teams The current dataset located at `$data->match->teams`.
     *
     * @return int
     */
    public static function get_team_index( $teams ) {
        if ( ! ( defined( 'MENS_EAGLES' ) && defined( 'MENS_SEVENS' ) && defined( 'WOMENS_EAGLES' ) && defined( 'WOMENS_SEVENS' ) ) ) {
            self::constants();
        }

        if ( in_array( $teams[0]->id, array( MENS_EAGLES, MENS_SEVENS, WOMENS_EAGLES, WOMENS_SEVENS ) ) ) {
            return (int) 0;
        }
        else {
            return (int) 1;
        }
    }

    /**
     * Get post ID from meta data.
     *
     * @since 1.0.0
     * @access protected
     * @static
     *
     * @param string $post_type  The post type slug.
     * @param string $meta_key   The meta key.
     * @param mixed  $meta_value The meta value.
     *
     * @return WP_Post|object The WordPress post object.
     */
    protected static function meta_get_post( $post_type, $meta_key, $meta_value ) {
        $args = array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'meta_key'       => $meta_key,
            'meta_value'     => $meta_value,
            'meta_compare'   => '=',
        );

        $posts = get_posts( $args );

        if ( ! empty( $posts[0] ) ) {
            return $posts[0];
        }

        return $posts;
    }

    /**
     * Create files for missing players.
     *
     * @return void
     */
    protected static function missing_players() {
		$missing = dirname(__FILE__) . '/missing.json';
		$women   = dirname(__FILE__) . '/players/womens-eagles';

		$data = self::get_file_data( $missing );

		foreach ( $data as $name => $content ) {
			$filename = $content->id;
			$path     = "{$women}/{$filename}.json";
			$cnt      = json_encode( $content );

			file_put_contents( $path, $cnt );
			echo "<pre>{$name}'s file has been created at {$path}</pre>";
		}
    }

    /**
     * Sort an associative array by specifying a key in any order.
     *
     * @static
     *
     * @uses WR_Utilities::_array_uksort()
     *
     * @param  array  $array Array to sort.
     * @param  string $key   Key to sort by. Use `.` to search additional level. Max 1 extra level.
     * @param  bool   $desc  PHP flag for {@see 'array_multisort'}. True = SORT_DESC. False = SORT_ASC.
     *
     * @return array         Sorted array.
     */
    public static function array_uksort( array &$array, string $key, bool $desc = true ) {
        if ( preg_match( '/\./', $key ) ) {
            $parts  = preg_split( '/\./', $key );
            $sorted = self::_array_uksort( $array, $parts[0], $parts[1] );
        } else {
            $sorted = self::_array_uksort( $array, $key );
        }

        if ( false !== $sorted ) {
            if ( ! $desc ) {
                array_multisort( $sorted, SORT_ASC, $array );
            }
            else {
                array_multisort( $sorted, SORT_DESC, $array );
            }
        }

        return $array;
    }

    /**
     * Ensure `$sorted` array is sorted accordingly.
     *
     * @access private
     * @static
     *
     * @see WR_Utilities::array_uksort()
     *
     * @param array       $array  Array to sort.
     * @param string      $key    Key to sort by.
     * @param string|null $subkey Optional nested key to use. Default is `null`.
     *
     * @return bool|array   False if `$array` is empty; sorted if not empty.
     */
    private static function _array_uksort( array $array, string $key, $subkey = null ) {
        if ( empty( $array ) ) {
            return false;
        }

        $sorted = array();

        foreach ( $array as $k => $row ) {
            if ( ! is_null( $subkey ) ) {
                $sorted[ $k ] = is_object( $row ) ? $row->{$key->$subkey} : $row[ $key[ $subkey ] ];
            } else {
                $sorted[ $k ] = is_object( $row ) ? $row->{$key} : $row[ $key ];
            }
        }

        return $sorted;
    }

    /**
     * Debug data.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param mixed $var Variable to debug.
     */
    protected static function debug( $var ) {
        wp_die( htmlentities( wp_json_encode( $var ) ) );
    }
}
