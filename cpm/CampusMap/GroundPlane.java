package CampusMap;

/**
 * <p>Title: CampusMap</p>
 *
 * <p>Description: Surface, where the scene takes place</p>
 *
 * <p>Copyright: Copyright (c) 2005</p>
 *
 * <p>Company: </p>
 *
 * @author David Hübner, Gunnar Dröge
 * @version 1.0
 */

public class GroundPlane {

//	public static final int x = -1140;
//	public static final int y = -1140;
//	public static final int width = 2280;
//	public static final int height = 2280;
	public static final int x = -1500 + 300;
	public static final int y = -750 + 125;
	public static final int width = 2600;
	public static final int height = 1500;
	public static final int elipseSize = 20000;
	
	public static final int lineDistance = 150;
	
	final static Color border_Color = new Color(240, 180, 170, 255);
	final static Color grid_Color = new Color(220, 220, 200, 255);
	
//	public static final int x = -50000;
//	public static final int y = -50000;
//	public static final int width = 100000;
//	public static final int height = 100000;
	private CampusMap applet;
	
	private int lineLengthX = width - (width%lineDistance);
	private int lineLengthY = height - (height%lineDistance);

	public GroundPlane(CampusMap p_applet) {
		applet = p_applet;
	}

	public void draw(){
		//coordinate system :)
    	applet.noStroke();
    	//applet.fill(50,80,255,20);
    	//applet.fill(117,148,117,255);
    	//applet.fill(167,198,167,255);
    	//applet.fill(255,255,240,255);
    	applet.fill(245,251,213,255);
    	applet.pushMatrix();
    	applet.translate(0,0,-2);
    	applet.ellipse(0, 0, elipseSize, elipseSize);
    	//applet.rect(x, y, width, width);
    	applet.popMatrix();
    	
    	//draw lines
    	applet.stroke(220,220,200,255);
    	for (int i=x; i <= (x + width); i+=lineDistance) {
    		if (i == x || i > x+width-lineDistance) {
    			applet.stroke(border_Color.getP5Color());
    			applet.line(i, y, 0, i, y + lineLengthY, 0);
    			applet.stroke(grid_Color.getP5Color());
    		}
    		else
    			applet.line(i, y, 0, i, y + lineLengthY, 0);
    	}
    	for (int i=y; i <= (y + height); i+=lineDistance) {
    		if (i == y || i > y+height-lineDistance) {
    			applet.stroke(border_Color.getP5Color());
    			applet.line(x, i, 0, x + lineLengthX, i, 0);
    			applet.stroke(grid_Color.getP5Color());
    		}
    		else
    			applet.line(x, i, 0, x + lineLengthX, i, 0);
    		
    	}
	}
	
	public FVector limit(FVector point) {
        if(point.getX() < x)			point.setX(x);
        if(point.getX() > x + width)	point.setX(x + width);
        if(point.getY() < y)			point.setY(y);
        if(point.getY() > y + height)	point.setY(y + height);
		return point;
	}
	
	public FVector check(FVector point) {
		FVector returnMe = new FVector(3);
        if(!( (point.getX() < x) || (point.getX() > x + width) || (point.getY() < y) || (point.getY() > y + height) ))
        	return returnMe;
    	if (point.getX() <= x)
    		returnMe.setX(point.getX()-x);
    	else if (point.getX() > x + width)
    		returnMe.setX(point.getX()-(x + width));
    	if (point.getY() <= y)
    		returnMe.setX(point.getY()-y);
    	else if (point.getY() > y + height)
    		returnMe.setY(point.getY()-(y + height));
    	return returnMe;
	}
	
	public boolean check(FVector point, float multiplier) {
        if( (point.getX() < x*multiplier) || (point.getX() > (x + width)*multiplier) || (point.getY() < y*multiplier) || (point.getY() > (y + height)*multiplier) )
        	return true;
        return false;
	}
	
	public FVector[] getRandomPositionsOnGroundPlane(int numberOfPositions, boolean avoidObjects,
			boolean avoidEachOther, float avoidingDistance, float groundPlaneMultiplier, boolean avoidGroundPlane) {
		
		FVector[] returnArray = new FVector[numberOfPositions];
		float minX = x * groundPlaneMultiplier;
		float maxX = width * groundPlaneMultiplier;
		float minY = y * groundPlaneMultiplier;
		float maxY = height * groundPlaneMultiplier;
		if (!avoidObjects && !avoidEachOther) {
			for (int i = 0; i < numberOfPositions; i++) {
				returnArray[i] = new FVector(applet.random(minX, maxX), applet.random(minY, maxY), 0);
			}
		} else {
			for (int i = 0; i < numberOfPositions; i++) {
				boolean foundPosition = false;
				while (!foundPosition) {
					returnArray[i] = new FVector(applet.random(minX, maxX), applet.random(minY, maxY), 0);
					foundPosition = true;
					if (avoidGroundPlane) {
						if (groundPlaneMultiplier > 1.0) {
							if (returnArray[i].e[0]>x&&returnArray[i].e[0]<width&&returnArray[i].e[1]>y&&returnArray[i].e[1]<height)
								foundPosition = false;
						} else System.err.println("getRandomPositionsOnGroundPlane: groundPlaneMultiplier < 1 && avoidGroundPlane does NOT work!");
					}
					if (avoidObjects && foundPosition && applet.objectManager.testBuildingsWithPoint(returnArray[i], avoidingDistance)!=null) {
						foundPosition = false;
					}
					if (avoidEachOther && foundPosition) {
						for (int n = 0; (n < i); n++) {
							if (returnArray[i].subtract(returnArray[n]).magnitudeSqr()<=(avoidingDistance*avoidingDistance*4)) {
								foundPosition = false;
								break;
							}
						}//end of for
					}//end of if
				}//end of while
			}//end of for
		} //end of else
		return returnArray;
	}
	
} //end of class GroundPlane