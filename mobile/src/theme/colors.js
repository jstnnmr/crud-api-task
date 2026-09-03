import { lightColors as colors, fonts } from './palette';

export { fonts, lightColors, darkColors } from './palette';

export const spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
};

export const borderRadius = {
  sm: 10,
  md: 14,
  lg: 18,
  xl: 24,
  full: 999,
};

export const shadows = {
  sm: {
    shadowColor: '#1F2623',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.07,
    shadowRadius: 10,
    elevation: 2,
  },
  md: {
    shadowColor: '#1F2623',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.08,
    shadowRadius: 20,
    elevation: 5,
  },
  lg: {
    shadowColor: '#1F2623',
    shadowOffset: { width: 0, height: 12 },
    shadowOpacity: 0.1,
    shadowRadius: 32,
    elevation: 10,
  },
};

export const typography = {
  displayLg: { fontFamily: fonts.display, fontSize: 44, lineHeight: 50 },
  displayMd: { fontFamily: fonts.display, fontSize: 32, lineHeight: 38 },
  titleLg: { fontFamily: fonts.uiBold, fontSize: 22, lineHeight: 28 },
  titleMd: { fontFamily: fonts.uiBold, fontSize: 18, lineHeight: 24 },
  titleSm: { fontFamily: fonts.uiBold, fontSize: 16, lineHeight: 22 },
  body: { fontFamily: fonts.uiRegular, fontSize: 15, lineHeight: 22 },
  bodyStrong: { fontFamily: fonts.uiSemiBold, fontSize: 15, lineHeight: 22 },
  label: { fontFamily: fonts.uiMedium, fontSize: 13, lineHeight: 18 },
  caption: { fontFamily: fonts.uiMedium, fontSize: 12, lineHeight: 16 },
  overline: {
    fontFamily: fonts.uiSemiBold,
    fontSize: 11,
    lineHeight: 14,
    letterSpacing: 0.5,
  },
  h1: { fontFamily: fonts.uiBold, fontSize: 28, lineHeight: 34 },
  h2: { fontFamily: fonts.uiBold, fontSize: 22, lineHeight: 28 },
  h3: { fontFamily: fonts.uiSemiBold, fontSize: 18, lineHeight: 24 },
  bodySmall: { fontFamily: fonts.uiRegular, fontSize: 13, lineHeight: 18 },
};

export default colors;
