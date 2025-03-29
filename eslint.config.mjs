import js from '../DPDocker/code/config/eslint.config.mjs';

export default [
	...js,
	{
		ignores: ['*/vendor/**/*.js']
	}
];
