package CampusMap;
/*
 * Created on 04.06.2005
 *
 * TODO To change the template for this generated file go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

/**
 * @author David
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */
public class Color {
	public	int	r,g,b,a;
	
	public Color(int _r, int _g, int _b, int _a) {
		r = _r;
		g = _g;
		b = _b;
		a = _a;
		check();
	}
	
	public Color(int _r, int _g, int _b) {
		r = _r;
		g = _g;
		b = _b;
		a = 255;
		check();
	}
	
	public Color(Color c2) {
		r = c2.r;
		g = c2.g;
		b = c2.b;
		a = c2.a;
	}
	
	public Color combine(Color c2) {
		return new Color((this.r+c2.r)/2, (this.g+c2.g)/2, (this.b+c2.b)/2, (this.a+c2.a)/2);
	}
	
	public Color combine(Color c2, float multiplier) {
		return new Color(
				(int)( (this.r*(1.0f-multiplier) + c2.r*multiplier) ),
				(int)( (this.g*(1.0f-multiplier) + c2.g*multiplier) ),
				(int)( (this.b*(1.0f-multiplier) + c2.b*multiplier) ),
				(int)( (this.a*(1.0f-multiplier) + c2.a*multiplier) )
				);
	}
	
	public void check() {
		if (a > 255) a = 255; else if (a < 0) a = 0;
		if (r > 255) r = 255; else if (r < 0) r = 0;
		if (g > 255) g = 255; else if (g < 0) g = 0;
		if (b > 255) b = 255; else if (b < 0) b = 0;
	}
	
	public Color(int p5Color) {
		//TODO
	}
	
	public int getP5Color() {
		check();
		return ((a << 24) | (r << 16) | (g << 8) | b);
	}
}