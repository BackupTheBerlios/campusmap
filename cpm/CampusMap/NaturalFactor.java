package CampusMap;

import hardcorepawn.fog.*;
import processing.core.PApplet;
import java.util.Calendar;
import java.util.Date;
import processing.core.*;

/*
 * Created on 05.02.2006
 *
 * TODO To change the template for this generated file go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

/**
 * @author David
 */
//class to manage create visual effects for the time and weather etc.

class NaturalFactor {
	
	private final static int updateTime	= 10000;//600000; //every 10 sec... do update
	private final static float Hoehe	= 53.87f; //every 30 minutes... do update
	private final static float Breite	= 10.68f; //every 30 minutes... do update
	//private final static int[] lengthOfMonths = {31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31}; 
	private CampusMap	applet;
	private fog myFog;
	private Color lightBlue;
	//Color blue;
	//Color white;
	private int timeLastUpdate = 0;
	private Sun sun;
	//private Color[] realFogColors;
	private Color[] fogColors;
	//private int[][] realFogDistances;
	private Color fogColor;
	private int[][] fogDistances;
	private Stars stars;
	private float fogNumber = 0.0f;
	
	
	public NaturalFactor(CampusMap p_applet){
		applet = p_applet;
		applet.env.objectInitDisplay.setText("NaturalFactor");
		lightBlue	= new Color(230, 241, 243);
		//blue		= new Color(105, 155, 204);
		//white		= new Color(255, 255, 255);
		sun = new Sun(applet);
//		myFog = new fog(applet);
//		myFog.setupFog(2000,8000);
//		myFog.setColor(lightBlue.getP5Color());
		fogColors = new Color[17];
		fogDistances = new int[17][2];
		fogColors[0] = new Color( 11,  11,  37);
		fogDistances[0][0] = 1500; fogDistances[0][1] = 6000;
		fogColors[1] = new Color(250, 223, 140);
		fogDistances[1][0] = 2000; fogDistances[1][1] = 8000;
		fogColors[2] = new Color(250, 223, 140);
		fogDistances[2][0] = 2000; fogDistances[2][1] = 8000;
		fogColors[3] = new Color(250, 223, 140);
		fogDistances[3][0] = 2000; fogDistances[3][1] = 8000;
		fogColors[4] = new Color(195, 213, 230);
		fogDistances[4][0] = 7000; fogDistances[4][1] = 12000;
		fogColors[5] = new Color(195, 213, 230);
		fogDistances[5][0] = 7000; fogDistances[5][1] = 12000;
		fogColors[6] = new Color(195, 213, 230);
		fogDistances[6][0] = 7000; fogDistances[6][1] = 12000;
		fogColors[7] = new Color(255, 255, 236);
		fogDistances[7][0] = 8000; fogDistances[7][1] = 15000;
		fogColors[8] = new Color(255, 255, 236);
		fogDistances[8][0] = 8000; fogDistances[8][1] = 15000;
		fogColors[9] = new Color(255, 255, 236);
		fogDistances[9][0] = 8000; fogDistances[9][1] = 15000;
		fogColors[10] = new Color(195, 213, 230);
		fogDistances[10][0] = 7000; fogDistances[10][1] = 13000;
		fogColors[11] = new Color(195, 213, 230);
		fogDistances[11][0] = 7000; fogDistances[11][1] = 13000;
		fogColors[12] = new Color(195, 213, 230);
		fogDistances[12][0] = 7000; fogDistances[12][1] = 13000;
		fogColors[13] = new Color(183, 162, 188);
		fogDistances[13][0] = 5000; fogDistances[13][1] = 8000;
		fogColors[14] = new Color(183, 162, 188);
		fogDistances[14][0] = 5000; fogDistances[14][1] = 8000;
		fogColors[15] = new Color(183, 162, 188);
		fogDistances[15][0] = 5000; fogDistances[15][1] = 8000;
		fogColors[16] = new Color(011, 011, 037);
		fogDistances[16][0] = 3000; fogDistances[16][1] = 6000;
//		fogColors = new Color[10000];
//		fogDistances = new int[10000][2];
//		for (int i = 0; i < fogColors.length; i++) {
//			fogColors[i] = realFogColors[i%7];
//			fogDistances[i] = realFogDistances[i%7];
//		}
		stars = new Stars(applet, 150, 30000, 300);
		
		update();
	}

	
	public void putIntoEffect() {
		if (applet.millis() % updateTime < 1000) {
			if (applet.millis() - timeLastUpdate > 1000) {
				timeLastUpdate = applet.millis();
				update();
			}
		}
		applet.background(lightBlue.r, lightBlue.g, lightBlue.b);
//		applet.directionalLight(240, 240, 240, -1, 1, -1);
//		applet.ambientLight(50,50,50,0,0,0);
//		applet.shininess(0.5f);
	}
	
	public void applySurroundings() {
		//enlightenSky();
		//myFog.doFog();
		if (((int)fogNumber) != 0 && ((int)fogNumber) != fogDistances.length-1) {
			//System.out.println("sun draw");
			sun.draw();
		}
		stars.draw();
	}
	
	private void update() {
		sun.compute(Hoehe, Breite, 1);
		double t_dayl = sun.getDayLength() + 4.0; //it's getting light ~two hour earlier and getting dark ~two hour later 
		double t_sunr = sun.getSunriseTime() - 1.0;
		double t_suns = sun.getSunsetTime() + 3.0;
		double lengthInMsPerFogColor = (t_dayl * 3600000 / (fogColors.length-1));
		double currentTimeFromSunrise = ((double)(PApplet.hour()*3600000+PApplet.minute()*60000+PApplet.second()*1000+System.currentTimeMillis()%1000) - t_sunr*3600000);
		if (currentTimeFromSunrise < 0) currentTimeFromSunrise = 0;
		else if (currentTimeFromSunrise > (t_suns - t_sunr)*3600000) currentTimeFromSunrise = (int)((t_suns - t_sunr)*3600000);
		fogNumber = (float)(currentTimeFromSunrise / lengthInMsPerFogColor);
		//System.out.println(" fognumber: " + fogNumber);
		fogColor = fogColors[(int)fogNumber].combine(fogColors[((int)fogNumber)>fogColors.length-2?0:((int)fogNumber)+1], fogNumber - ((int)fogNumber));
		int fogNearDist	= (int)(fogDistances[(int)fogNumber][0] * (1.0f - (fogNumber - ((int)fogNumber))) + fogDistances[((int)fogNumber)>fogColors.length-2?0:((int)fogNumber)+1][0] * (fogNumber - ((int)fogNumber)));
		int fogFarDist	= (int)(fogDistances[(int)fogNumber][1] * (1.0f - (fogNumber - ((int)fogNumber))) + fogDistances[((int)fogNumber)>fogColors.length-2?0:((int)fogNumber)+1][1] * (fogNumber - ((int)fogNumber)));
//		myFog.setupFog(fogNearDist, fogFarDist);
//		myFog.setColor(fogColor.getP5Color());
		//System.out.println("fogNearDist " + fogNearDist + " fogFarDist " + fogFarDist + "color: " + fogColor.getP5Color() + " " + fogColor.r + " " + fogColor.g + " " + fogColor.b);
	}
	
	//schmu so far
	public void enlightenSky() {
		applet.hint(PConstants.DISABLE_DEPTH_TEST);
		float height = applet.theCamera.getPos().getZ() + 200;
		applet.noStroke();
		Color skyColor = new Color(fogColor);
		skyColor.b *= 1.1f;
		skyColor.r /= 1.1f;
		skyColor.g /= 1.1f;
		skyColor.check();
		applet.fill(skyColor.getP5Color());
		applet.beginShape();
		applet.vertex(GroundPlane.x*2, GroundPlane.y*2, height);
		applet.vertex(GroundPlane.width*2, GroundPlane.y*2, height);
		applet.vertex(GroundPlane.width*2, GroundPlane.height*2, height);
		applet.vertex(GroundPlane.x*2, GroundPlane.height*2, height);
		applet.endShape();
		applet.noHint(PConstants.DISABLE_DEPTH_TEST);
	}
}

// This class wil collect the date values and methods
class CurrentTime {
	public int monat,tag;
	public double stunde;
	public String ErrorMsg;
	
	public void setCurrentTime(int p_monat, int p_tag, double p_stunde) {
		
		monat	= p_monat;
		tag		= p_tag;
		stunde	= p_stunde;
		
		// Chech validity of the values and force reasonable values
		// Show illegal value messages on statusline
		// Clear the errormsg first:
		ErrorMsg =" ";
		
		if (monat<1) {ErrorMsg ="Month? " + monat; monat = 1; }
		else if (monat>12) {ErrorMsg+=" Month? " + monat; monat = 12; }
		
		if (tag<1) {ErrorMsg +=" Day? " + tag; tag = 1; }
		else if (tag >31) {ErrorMsg +=" Day? " + tag; tag = 31; }
		
		if (stunde<0.0) {ErrorMsg +=" Hour? " + stunde; stunde = 0.0;}
		else if (stunde>24.0) {ErrorMsg +=" Hour? " + stunde; stunde = 24.0;}
		if (!ErrorMsg.equals(" ")) System.out.println(ErrorMsg);
	}
}

class Sun {
	final double pi = 3.141592654;
	final double tpi = 2.0 * pi;
	final double degs = 180.0/pi;
	final double rads = pi/180.0;
	
	CampusMap applet;
	double L,RA,daylen,delta,x,y,z;
	double riset,settm,altmax,altmin,noont,midnt,azim,altit;

	int year,month,day;
	
	CurrentTime setdate = new CurrentTime();
	
	final double AirRefr = 34.0/60.0; // athmospheric refraction degrees //
	
	String tSyote = null;
	
	public Sun(CampusMap p_applet){
		applet = p_applet;
		year = PApplet.year();
		month = PApplet.month();
		day = PApplet.day();
	}
	
	//   Get the days to J2000
	//   h is UT in decimal hours
	//   FNday only works between 1901 to 2099 - see Meeus chapter 7
	
	float Round2d3(double x) {
		double z = (1000.0*x + 0.499);
		int i = (int)(z);
		z = ((float)i/1000.0);
		return (float)z;
	}
	
	public double FNday (int y, int m, int d, double h) {
		long luku = - 7 * (y + (m + 9)/12)/4 + 275*m/9 + d;
		// type casting necessary on PC DOS and TClite to avoid overflow
		luku+= (long)y*367;
		return (double)luku - 730530.0 + h/24.0;
	};
	
	//   the function below returns an angle in the range
	//   0 to 2*pi
	
	public double FNrange(double x) {
	    double b = x / tpi;
	    double a = tpi * (b - (long)(b));
	    if (a < 0) a = tpi + a;
	    return a;
	};
	
	// Calculating the hourangle
	
	public double f0(double lat, double declin) {
		double fo, dfo;
		double SunDia = 0.53;     // Sunradius degrees
		
		dfo = rads*(0.5*SunDia + AirRefr);
		if (lat < 0.0) dfo = -dfo;      // Southern hemisphere
		fo = Math.tan(declin + dfo) * Math.tan(lat*rads);
		if (fo>0.99999) fo=1.0; // to avoid overflow //
		fo = Math.asin(fo) + pi/2.0;
		return fo;
	};
	
	//   Find the ecliptic longitude of the Sun
	
	public double FNsun (double d) {
		double w,M,v,r,g;
		//   mean longitude of the Sun
		w = 282.9404 + 4.70935E-5 * d;
		M = 356.047 + 0.9856002585 * d;
		// Sun's mean longitude
		L = FNrange(w * rads + M * rads);
		
		//   mean anomaly of the Sun
		
		g = FNrange(M * rads);
		
		// eccentricity
		double ecc = 0.016709 - 1.151E-9 * d;
		
		//   Obliquity of the ecliptic
		
		double obliq = 23.4393 * rads - 3.563E-7 * rads * d;
		double E = M + degs * ecc * Math.sin(g) * (1.0 + ecc * Math.cos(g));
		E = degs*FNrange(E*rads);
		x = Math.cos(E*rads) - ecc;
		y = Math.sin(E*rads) * Math.sqrt(1.0 - ecc*ecc);
		r = Math.sqrt(x*x + y*y);
		v = Math.atan2(y,x)*degs;
		// longitude of sun
		double lonsun = v + w;
		if (lonsun>360.0) lonsun-= 360.0;
		
		// sun's ecliptic rectangular coordinates
		x = r * Math.cos(lonsun*rads);
		y = r * Math.sin(lonsun*rads);
		double yequat = y * Math.cos(obliq);
		double zequat = y * Math.sin(obliq);
		RA = Math.atan2(yequat,x);
		delta = Math.atan2(zequat,Math.sqrt(x*x + yequat*yequat));
		RA*= degs;
		
		//   Ecliptic longitude of the Sun
		
		return FNrange(L + 1.915 * rads * Math.sin(g) + .02 * rads * Math.sin(2 * g));
	};

	// Display decimal hours in hours and minutes
	String showhrmn(double dhr) {
		int hr,mn;
		hr=(int) dhr;
		String hrs, mns;
		mn = (int)((dhr - (double) hr)*60);
		hrs = " " + hr; mns = ":" + mn;
		if (hr < 10) hrs = "0" + hr;
		if (mn < 10) mns = ":0" +mn;
		return (hrs +  mns);
	};

	public void compute(double latit, double longit, double tzone) {
		int m;
		double h;
		
		//  get the date and time from the user
		setdate.setCurrentTime(PApplet.month(),PApplet.day(),PApplet.hour());
		m = setdate.monat; day = setdate.tag;
		h = setdate.stunde;
		// Show allways status to clear it if no error
		
		// Input latitude, longitude and timezone
		
		double UT = h - tzone;  // universal time
		double jd = FNday(year, m, day, UT);
		
		//   Use FNsun to find the ecliptic longitude of the
		//   Sun
		double lambda = FNsun(jd);
		//   Obliquity of the ecliptic
		
		double obliq = 23.4393 * rads - 3.563E-7 * rads * jd;
		
		// Sidereal time at Greenwich meridian
		double GMST0 = L*degs/15.0 + 12.0;      // hours
		double SIDTIME = GMST0 + UT + longit/15.0;
		// Hour Angle
		double ha = 15.0*SIDTIME - RA;  // degrees
		           ha = FNrange(rads*ha);
		x = Math.cos(ha) * Math.cos(delta);
		y = Math.sin(ha) * Math.cos(delta);
		z = Math.sin(delta);
		double xhor = x * Math.sin(latit*rads) - z * Math.cos(latit*rads);
		double yhor = y;
		double zhor = x * Math.cos(latit*rads) + z * Math.sin(latit*rads);
		azim = Math.atan2(yhor,xhor) + pi;
		                azim = FNrange(azim);
		altit = Math.asin(zhor) * degs;
		// Include Air refraction if altitude less than 30 degrees
		if (altit < 30.0) altit+= AirRefr;
		
		double alpha = Math.atan2(Math.cos(obliq) * Math.sin(lambda), Math.cos(lambda));
		
		//   Find the Equation of Time in minutes
		double equation = 1440.0 - (L - alpha) * degs * 4.0;
		
		ha = f0(latit,delta);
		
		// Conversion of angle to hours and minutes //
		daylen = degs*ha/7.5;
		     if (daylen<0.0001) {daylen = 0.0;}
		// arctic winter     //
		//String se =" (S)";
		riset = 12.0 - 12.0 * ha/pi + tzone - longit/15.0 + equation/60.0;
		settm = 12.0 + 12.0 * ha/pi + tzone - longit/15.0 + equation/60.0;
		noont = riset + 12.0 * ha/pi;
		midnt = noont -12.0;
		altmax = 90.0 + delta*degs - latit; 
		altmin = altmax + 2.0*latit -180.0;
		if (altmax > 90.0) altmax=180.0 - altmax; //se =" (N)";} // around the equator and in south
		if (altmin < -90.0) altmin = -(altmin + 180.0);
		if (altmax < 30.0) altmax+= AirRefr;            // Airrefraction included at small altitudes
		if (altmin > -30.0) altmin+= AirRefr;
		
		if (noont>24.0) noont-= 24.0;
		if (midnt<0.0) midnt+= 24.0;
		if (riset > 24.0) riset-= 24.0;
		// sometimes Sunrise may take place before midnight
		// We must correct the negative decimal hour 
		if (riset < 0.0) riset+= 24.0;
		if (settm > 24.0) settm-= 24.0;
		
		riset = addSummertime(riset);
		settm = addSummertime(settm);
		
//		System.out.println(String.valueOf(Round2d3(delta * degs)));
//		System.out.println(showhrmn(daylen));
//		System.out.println(showhrmn(addSummertime(riset)));
//		System.out.println(showhrmn(addSummertime(settm)));
//		System.out.println(String.valueOf(Round2d3(altmax)) + se);
//		System.out.println(String.valueOf(Round2d3(altmin)));
//		System.out.println(showhrmn(noont));
//		System.out.println(showhrmn(midnt));
//		System.out.println(String.valueOf(Round2d3(azim*degs)));
//		System.out.println(String.valueOf(Round2d3(altit)));
	}
	
	public void draw() {
		FVector toSun = new FVector(1,0,0);
		toSun.rotateMeY(PApplet.radians((float)altit));
		toSun.rotateMeZ((float)azim - 0.237f); //adjustment for the groundplane rotation
		toSun.multiplyMe(100000);
		applet.sphereDetail(10);
		applet.noStroke();
		applet.fill(255, 255, (int)applet.random(20, 100), (int)applet.random(180, 200));
		applet.pushMatrix();
		applet.translate(toSun.e[0], toSun.e[1], toSun.e[2]);
		applet.sphere(5000);
		applet.popMatrix();
		applet.fill(255, 255, 255, 255);
		//System.out.println("azim: " + applet.degrees((float)azim) + " altit: " + (float)altit);
		//toSun.printMe();
	}
	
	public double getSunriseTime() {
		return riset;
	}
	
	public double getSunsetTime() {
		return settm;
	}
	
	public double getDayLength() {
		return daylen;
	}
	
	public double addSummertime(double time) {
		if (month >3 && month<10) {
			//System.out.println("add");
			return time+1;
		}
		else if (month <3 || month>10){
			//System.out.println("noadd");
			return time;
		}
		else if (month==3) {
			boolean found = false;
			int counter = 0;
			Calendar calendar = Calendar.getInstance();
			Date date;
			while (!found) {
				date = new Date(PApplet.year(), PApplet.month(), PApplet.day()-counter);
				calendar.setTime(date);
				if ( (calendar.get(Calendar.DAY_OF_WEEK)-1) ==0)
					found = true;
				else counter++;
			}
			if (PApplet.day()>=31-counter){
				//System.out.println("add");
				return time+1;
			}
			else{
				//System.out.println("noadd");
				return time;
			}
		}
		else {
			boolean found = false;
			int counter = 0;
			Calendar calendar = Calendar.getInstance();
			Date date;
			while (!found) {
				date = new Date(PApplet.year(), PApplet.month(), PApplet.day()-counter);
				calendar.setTime(date);
				if ( (calendar.get(Calendar.DAY_OF_WEEK)-1) ==0)
					found = true;
				else counter++;
			}
			if (PApplet.day()<31-counter){
				//System.out.println("add");
				return time+1;
			}
			else{
				//System.out.println("noadd");
				return time;
			}
		}
	}
	
}

class Stars {
	
	private CampusMap applet;
	private int height;
	private int width;
	private int positions[][];
	
	public Stars(CampusMap p_applet, int numStars, int p_height, int p_width) {
		applet	= p_applet;
		width	= p_width;
		height	= p_height;
		positions = new int[numStars][2];
		FVector[] posVectors = applet.groundPlane.getRandomPositionsOnGroundPlane(numStars, false, false, 0.0f, 100.0f, false);
		for (int i=0; i<numStars; i++) {
			positions[i][0] = (int)posVectors[i].getX();
			positions[i][1] = (int)posVectors[i].getY();
		}
	}
	
	public void draw() {
		applet.noStroke();
		for (int i=0; i<positions.length; i++) {
			int c = (int)applet.random(210, 240);
			applet.fill(c, c, 255);
			applet.pushMatrix();
			applet.translate(positions[i][0], positions[i][1], height);
			applet.beginShape(PConstants.TRIANGLES); 
			applet.vertex( 0*width,  1*width, 0); 
			applet.vertex( 1*width, -1*width, 0); 
			applet.vertex(-1*width, -1*width, 0); 
			applet.endShape(); 
			applet.popMatrix();
		}
		
		//clouds tryout
//		int height = 100;
//		applet.noStroke();
//		applet.fill(255,255,255,255);
//		PImage a = applet.loadImage("clouds.jpg");
//		applet.textureMode(applet.NORMALIZED); 
//		applet.beginShape(applet.TRIANGLES);
//		applet.texture(a);
//		applet.vertex(0, 0, height, 0, 0);
//		applet.vertex(1000, 0, height, 1, 0);
//		applet.vertex(1000, 1000, height, 1, 1);
//		applet.vertex(applet.groundPlane.x*2, applet.groundPlane.y*2, height, 0, 0);
//		applet.vertex(applet.groundPlane.width*2, applet.groundPlane.y*2, height, 1, 0);
//		applet.vertex(applet.groundPlane.width*2, applet.groundPlane.height*2, height, 1, 1);
//		applet.vertex(applet.groundPlane.x*2, applet.groundPlane.height*2, height, 0, 1);
//		applet.endShape();

	}
}