'use strict';

const { action, assert, REST, utils } = require( 'api-testing' );
const supertest = require( 'supertest' );

const chai = require( 'chai' );
const expect = chai.expect;

const chaiResponseValidator = require( 'chai-openapi-response-validator' ).default;

let pathPrefix = '/content/v1';
let specModule = '/content/v1';

describe( 'POST /page', () => {
	let client, mindy, anon, anonToken, openApiSpec;

	beforeEach( async () => {
		// Reset the client and token before each test
		// In a temp account context, making an anonymous edit generates an account
		// so we want to reset state after each edit
		mindy = await action.mindy();
		client = new REST( 'rest.php' );
		anon = await action.getAnon();
		anonToken = await anon.token();

		const specPath = '/specs/v0/module' + specModule;
		const { status, text } = await client.get( specPath );
		assert.deepEqual( status, 200, text );

		openApiSpec = JSON.parse( text );
		chai.use( chaiResponseValidator( openApiSpec ) );

	} );

	const checkEditResponse = function ( title, reqBody, body ) {
		assert.containsAllKeys( body, [ 'title', 'key', 'source', 'latest', 'id',
			'license', 'content_model' ] );
		assert.containsAllKeys( body.latest, [ 'id', 'timestamp' ] );
		assert.nestedPropertyVal( body, 'source', reqBody.source );
		assert.nestedPropertyVal( body, 'title', title );
		assert.nestedPropertyVal( body, 'key', utils.dbkey( title ) );
		assert.isAbove( body.latest.id, 0 );

		if ( reqBody.content_model ) {
			assert.nestedPropertyVal( body, 'content_model', reqBody.content_model );
		}
	};

	const checkSourceResponse = function ( title, reqBody, body ) {
		if ( reqBody.content_model ) {
			assert.nestedPropertyVal( body, 'content_model', reqBody.content_model );
		}

		assert.nestedPropertyVal( body, 'title', title );
		assert.nestedPropertyVal( body, 'key', utils.dbkey( title ) );
		assert.nestedPropertyVal( body, 'source', reqBody.source );
	};

	describe( 'successful operation', () => {
		it( 'should create a page if it does not exist', async () => {
			const titleSuffix = utils.title();
			const title = 'A B+C:D@E-' + titleSuffix;
			const normalizedTitle = utils.dbkey( title );

			// In "title style" encoding, spaces turn to underscores,
			// colons are preserved, and slashes and pluses get encoded.
			// FIXME: correct handling of encoded slashes depends on
			//        the server setup and can't be tested reliably.
			const encodedTitle = 'A_B%2BC:D@E-' + titleSuffix;

			const reqBody = {
				token: anonToken,
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				title
			};

			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header } = newPage;
			assert.equal( editStatus, 201 );
			assert.match( header[ 'content-type' ], /^application\/json/ );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage ).to.satisfyApiSpec;

			assert.nestedProperty( header, 'location' );
			const location = header.location;
			assert.match( location, new RegExp( `^https?://.*/v1/page/${ encodedTitle }$` ) );
			checkEditResponse( title, reqBody, editBody );

			// follow redirect
			const { status: redirStatus, body: redirBody, header: redirHeader } =
				await supertest.agent( location ).get( '' );
			assert.equal( redirStatus, 200 );
			assert.match( redirHeader[ 'content-type' ], /^application\/json/ );
			checkSourceResponse( title, reqBody, redirBody );

			// construct request to fetch content
			const res = await client.get( `${ pathPrefix }/page/${ normalizedTitle }` );
			const { status: sourceStatus, body: sourceBody, header: sourceHeader } = res;
			assert.equal( sourceStatus, 200 );
			assert.match( sourceHeader[ 'content-type' ], /^application\/json/ );
			checkSourceResponse( title, reqBody, sourceBody );

			// eslint-disable-next-line no-unused-expressions
			expect( res ).to.satisfyApiSpec;

		} );

		it( 'should create a page with specified model', async () => {
			const title = utils.title( 'Edit Test ' );
			const normalizedTitle = utils.dbkey( title );

			// TODO: Test with a model different from the default. This however requires
			//       the changecontentmodel permission, which anons don't have.
			const reqBody = {
				token: anonToken,
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				content_model: 'wikitext',
				title
			};

			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header: editHeader } = newPage;

			assert.equal( editStatus, 201 );
			assert.match( editHeader[ 'content-type' ], /^application\/json/ );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage ).to.satisfyApiSpec;
			checkEditResponse( title, reqBody, editBody );

			const res = await client.get( `${ pathPrefix }/page/${ normalizedTitle }` );
			const { status: sourceStatus, body: sourceBody, header: sourceHeader } = res;
			assert.equal( sourceStatus, 200 );
			assert.match( sourceHeader[ 'content-type' ], /^application\/json/ );
			checkSourceResponse( title, reqBody, sourceBody );

			// eslint-disable-next-line no-unused-expressions
			expect( res ).to.satisfyApiSpec;

		} );
	} );

	describe( 'request validation', () => {
		const requiredProps = [ 'source', 'comment', 'title' ];

		requiredProps.forEach( ( missingPropName ) => {
			const title = utils.title( 'Edit Test ' );
			const reqBody = {
				token: anonToken,
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				title
			};

			it( `should fail when ${ missingPropName } is missing from the request body`, async () => {
				const incompleteBody = { ...reqBody };
				delete incompleteBody[ missingPropName ];

				const newPage = await client.post( `${ pathPrefix }/page`, incompleteBody );
				const { status: editStatus, body: editBody, header: editHeader } = newPage;

				assert.equal( editStatus, 400 );
				assert.match( editHeader[ 'content-type' ], /^application\/json/ );
				assert.nestedProperty( editBody, 'messageTranslations' );
				// eslint-disable-next-line no-unused-expressions
				expect( newPage.text ).to.satisfySchemaInApiSpec( 'GenericErrorResponseModel' );
			} );
		} );

		it( 'should fail if no token is given', async () => {
			const title = utils.title( 'Edit Test ' );
			const reqBody = {
				// no token
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				title
			};

			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header: editHeader } = newPage;

			assert.equal( editStatus, 403 );
			assert.match( editHeader[ 'content-type' ], /^application\/json/ );
			assert.nestedProperty( editBody, 'messageTranslations' );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage.text ).to.satisfySchemaInApiSpec( 'GenericErrorResponseModel' );
		} );

		it( 'should fail if a bad token is given', async () => {
			const title = utils.title( 'Edit Test ' );
			const reqBody = {
				token: 'BAD',
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				title
			};

			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header: editHeader } = newPage;

			assert.equal( editStatus, 403 );
			assert.match( editHeader[ 'content-type' ], /^application\/json/ );
			assert.nestedProperty( editBody, 'messageTranslations' );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage.text ).to.satisfySchemaInApiSpec( 'GenericErrorResponseModel' );
		} );

		it( 'should fail if a bad content model is given', async () => {
			const title = utils.title( 'Edit Test ' );

			const reqBody = {
				token: anonToken,
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				content_model: 'THIS DOES NOT EXIST!',
				title
			};
			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header: editHeader } = newPage;

			assert.equal( editStatus, 400 );
			assert.match( editHeader[ 'content-type' ], /^application\/json/ );
			assert.nestedProperty( editBody, 'messageTranslations' );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage.text ).to.satisfySchemaInApiSpec( 'GenericErrorResponseModel' );

		} );

		it( 'should fail if a bad title is given', async () => {
			const title = '_|_'; // not a valid page title

			const reqBody = {
				token: anonToken,
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				title
			};
			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header: editHeader } = newPage;

			assert.equal( editStatus, 400 );
			assert.match( editHeader[ 'content-type' ], /^application\/json/ );
			assert.nestedProperty( editBody, 'messageTranslations' );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage.text ).to.satisfySchemaInApiSpec( 'GenericErrorResponseModel' );
		} );
	} );

	describe( 'failures due to system state', () => {
		it( 'should detect a conflict if page exist', async () => {
			const title = utils.title( 'Edit Test ' );

			// create
			await mindy.edit( title, {} );

			const reqBody = {
				token: anonToken,
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				title
			};
			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header: editHeader } = newPage;

			assert.equal( editStatus, 409 );
			assert.match( editHeader[ 'content-type' ], /^application\/json/ );
			assert.nestedProperty( editBody, 'messageTranslations' );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage.text ).to.satisfySchemaInApiSpec( 'GenericErrorResponseModel' );
		} );
	} );

	describe( 'permission checks', () => {
		it( 'should fail when trying to create a protected title without appropriate permissions', async () => {
			const title = utils.title( 'Edit Test ' );

			// protected a non-existing title against creation
			await mindy.action( 'protect', {
				title,
				token: await mindy.token(),
				protections: 'create=sysop'
			}, 'POST' );

			const reqBody = {
				token: anonToken,
				source: 'Lörem Ipsüm',
				comment: 'tästing',
				title
			};
			const newPage = await client.post( `${ pathPrefix }/page`, reqBody );
			const { status: editStatus, body: editBody, header: editHeader } = newPage;

			assert.equal( editStatus, 403 );
			assert.match( editHeader[ 'content-type' ], /^application\/json/ );
			assert.nestedProperty( editBody, 'messageTranslations' );
			// eslint-disable-next-line no-unused-expressions
			expect( newPage.text ).to.satisfySchemaInApiSpec( 'GenericErrorResponseModel' );
		} );

	} );
} );

// eslint-disable-next-line mocha/no-exports
exports.init = function ( pp, sm ) {
	// Allow testing both legacy and module paths using the same tests
	pathPrefix = pp;
	specModule = sm;
};
