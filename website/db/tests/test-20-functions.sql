-- Start transaction and plan the tests.
BEGIN;

-- SELECT * FROM no_plan();
SELECT plan(86);

-- Run the tests.
SELECT is(valid_move_types(), '{0,1,2,3,4,5}'::smallint[], 'Check valid_move_types()');
SELECT is(move_counting_kilometers(), '{0,3,5}'::smallint[], 'Check move_counting_kilometers()');
SELECT is(move_requiring_coordinates(), '{0,3,5}'::smallint[], 'Check move_requiring_coordinates()');
SELECT is(valid_moves_comments_types(), '{0,1}'::smallint[], 'Check valid_moves_comments_types()');
SELECT is(moves_types_markable_as_missing(), '{0,3}'::smallint[], 'Check moves_types_markable_as_missing()');
SELECT is(moves_type_last_position(), '{0,1,3,4,5}'::smallint[], 'Check moves_type_last_position()');
SELECT is(moves_type_hold(), '{1,5}'::smallint[], 'Check moves_type_hold()');
SELECT is(valid_email_revalidate_used(), '{0,1,2,3}'::smallint[], 'Check validate_email_revalidate_used()');

SELECT is(validate_move_types(0::smallint), TRUE, 'Check validate_move_types(0)');
SELECT is(validate_move_types(1::smallint), TRUE, 'Check validate_move_types(1)');
SELECT is(validate_move_types(2::smallint), TRUE, 'Check validate_move_types(2)');
SELECT is(validate_move_types(3::smallint), TRUE, 'Check validate_move_types(3)');
SELECT is(validate_move_types(4::smallint), TRUE, 'Check validate_move_types(4)');
SELECT is(validate_move_types(5::smallint), TRUE, 'Check validate_move_types(5)');
SELECT is(validate_move_types(6::smallint), FALSE, 'Check validate_move_types(6)');
SELECT is(validate_move_types(-1::smallint), FALSE, 'Check validate_move_types(-1)');

SELECT lives_ok('SELECT move_type_count_kilometers(0::smallint)');
SELECT throws_ok('SELECT move_type_count_kilometers(6::smallint)');

SELECT ok(move_type_count_kilometers(0::smallint) = TRUE, 'Type 0 is counting KM');
SELECT ok(move_type_count_kilometers(1::smallint) = FALSE, 'Type 1 is NOT counting KM');
SELECT ok(move_type_count_kilometers(2::smallint) = FALSE, 'Type 2 is NOT counting KM');
SELECT ok(move_type_count_kilometers(3::smallint) = TRUE, 'Type 3 is counting KM');
SELECT ok(move_type_count_kilometers(4::smallint) = FALSE, 'Type 4 is NOT counting KM');
SELECT ok(move_type_count_kilometers(5::smallint) = TRUE, 'Type 5 is counting KM');

SELECT is(validate_moves_comments_type(0::smallint), TRUE, 'Check validate_moves_comments_type(0)');
SELECT is(validate_moves_comments_type(1::smallint), TRUE, 'Check validate_moves_comments_type(1)');
SELECT is(validate_moves_comments_type(2::smallint), FALSE, 'Check validate_moves_comments_type(2)');

SELECT is(moves_type_waypoint(0::smallint, NULL), TRUE, 'Check moves_type_waypoint(NULL)');
SELECT is(moves_type_waypoint(1::smallint, NULL), TRUE, 'Check moves_type_waypoint(NULL)');
SELECT is(moves_type_waypoint(2::smallint, NULL), TRUE, 'Check moves_type_waypoint(NULL)');
SELECT is(moves_type_waypoint(3::smallint, NULL), TRUE, 'Check moves_type_waypoint(NULL)');
SELECT is(moves_type_waypoint(4::smallint, NULL), TRUE, 'Check moves_type_waypoint(NULL)');
SELECT is(moves_type_waypoint(5::smallint, NULL), TRUE, 'Check moves_type_waypoint(NULL)');

SELECT is(moves_type_waypoint(0::smallint, 'GC5BRQK'), TRUE, 'Check moves_type_waypoint(GC5BRQK)');
SELECT throws_ok($$SELECT moves_type_waypoint(1::smallint, 'GC5BRQK')$$);
SELECT throws_ok($$SELECT moves_type_waypoint(2::smallint, 'GC5BRQK')$$);
SELECT is(moves_type_waypoint(3::smallint, 'GC5BRQK'), TRUE, 'Check moves_type_waypoint(GC5BRQK)');
SELECT throws_ok($$SELECT moves_type_waypoint(4::smallint, 'GC5BRQK')$$);
SELECT is(moves_type_waypoint(5::smallint, 'GC5BRQK'), TRUE, 'Check moves_type_waypoint(GC5BRQK)');

SELECT lives_ok($$SELECT moves_type_waypoint(0::smallint, '')$$); --
SELECT throws_ok($$SELECT moves_type_waypoint(1::smallint, '')$$);
SELECT throws_ok($$SELECT moves_type_waypoint(2::smallint, '')$$);
SELECT lives_ok($$SELECT moves_type_waypoint(3::smallint, '')$$); --
SELECT throws_ok($$SELECT moves_type_waypoint(4::smallint, '')$$);
SELECT lives_ok($$SELECT moves_type_waypoint(5::smallint, '')$$); --

SELECT is(moves_check_author_username(1::smallint, NULL), TRUE, 'Check moves_check_author_username(1, NULL)');
SELECT is(moves_check_author_username(1::smallint, ''), FALSE, 'Check moves_check_author_username(1, EMPTY)');
SELECT is(moves_check_author_username(1::smallint, 'user'), FALSE, 'Check moves_check_author_username(1, user)');
SELECT is(moves_check_author_username(NULL, NULL), FALSE, 'Check moves_check_author_username(NULL, NULL)');
SELECT is(moves_check_author_username(NULL, ''), FALSE, 'Check moves_check_author_username(NULL, EMPTY)');
SELECT is(moves_check_author_username(NULL, 'user'), TRUE, 'Check moves_check_author_username(1, user)');

SELECT is(coords2position(43.68579, 6.87647), '0101000020E610000053B3075A81811B4040F67AF7C7D74540', 'Check conversion coordinates to position');
SELECT is(position2coords('0101000020E610000053B3075A81811B4040F67AF7C7D74540'), ROW(43.68579::double precision, 6.87647::double precision), 'Check conversion position to coordinates');

SELECT ok(LENGTH(generate_tracking_code()) = 6, 'Secret id size');
SELECT ok(LENGTH(generate_tracking_code(10)) = 10, 'Secret id size');

SELECT is(is_tracking_code_valid('123456'), TRUE);
SELECT is(is_tracking_code_valid('ABCDEF'), TRUE);
SELECT is(is_tracking_code_valid('ABC123'), TRUE);
SELECT is(is_tracking_code_valid(''), FALSE);
SELECT is(is_tracking_code_valid('1'), FALSE);
SELECT is(is_tracking_code_valid('12345'), FALSE);
SELECT is(is_tracking_code_valid('GK'), FALSE);
SELECT is(is_tracking_code_valid('GC'), FALSE);
SELECT is(is_tracking_code_valid('OP'), FALSE);
SELECT is(is_tracking_code_valid('OK'), FALSE);
SELECT is(is_tracking_code_valid('GE'), FALSE);
SELECT is(is_tracking_code_valid('OZ'), FALSE);
SELECT is(is_tracking_code_valid('OU'), FALSE);
SELECT is(is_tracking_code_valid('ON'), FALSE);
SELECT is(is_tracking_code_valid('OL'), FALSE);
SELECT is(is_tracking_code_valid('OJ'), FALSE);
SELECT is(is_tracking_code_valid('OS'), FALSE);
SELECT is(is_tracking_code_valid('GD'), FALSE);
SELECT is(is_tracking_code_valid('GA'), FALSE);
SELECT is(is_tracking_code_valid('VI'), FALSE);
SELECT is(is_tracking_code_valid('MS'), FALSE);
SELECT is(is_tracking_code_valid('TR'), FALSE);
SELECT is(is_tracking_code_valid('EX'), FALSE);
SELECT is(is_tracking_code_valid('GR'), FALSE);
SELECT is(is_tracking_code_valid('RH'), FALSE);
SELECT is(is_tracking_code_valid('OX'), FALSE);
SELECT is(is_tracking_code_valid('OB'), FALSE);
SELECT is(is_tracking_code_valid('OR'), FALSE);
SELECT is(is_tracking_code_valid('LT'), FALSE);
SELECT is(is_tracking_code_valid('LV'), FALSE);

SELECT ok(fresher_than('2020-04-07 00:00:00+00'::timestamp with time zone, 100, 'YEAR') = TRUE, 'Older than 100 years');


-- Finish the tests and clean up.
SELECT * FROM finish();
ROLLBACK;
