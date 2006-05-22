package CampusMap;

import processing.core.PConstants;

//class that stores the coordinates of a rectangle
//for now limited to axis aligned rectangles
//provides methods to test this rectangle with rays for intersection
//also provides methods to scale and move the rectangle in worldspace

public class CollisionRectangle {
	
	private	FVector m_vCorner1;
	private	FVector m_vCorner2;
	private int		m_nAlignedToAxis;
	
	public CollisionRectangle(FVector p_vCorner1, FVector p_vCorner2, int p_nAlignedToAxis) {
		if (p_vCorner1.e.length != 3 || p_vCorner2.e.length != 3 || m_nAlignedToAxis > 2)
			System.err.println("CollisionRectangle: Can't initialize CollisionRectangle with vector-demensions azer then 3.");
		
		m_vCorner1			= p_vCorner1;
		m_vCorner2			= p_vCorner2;
		m_nAlignedToAxis	= p_nAlignedToAxis;
		
		if (m_vCorner1.e[m_nAlignedToAxis] != m_vCorner2.e[m_nAlignedToAxis])
		{
			m_vCorner1.e[m_nAlignedToAxis] = m_vCorner2.e[m_nAlignedToAxis] =
				m_vCorner1.e[m_nAlignedToAxis] + m_vCorner2.e[m_nAlignedToAxis] /2;
		}
		m_vCorner1.printMe("mvCorner1 init");
		m_vCorner2.printMe("mvCorner2 init");
	}
	
	public void moveAbout(FVector about) {
		about.printMe();
		if (about.getLength() == 3)
		{
			m_vCorner1.addMe(about);
			m_vCorner2.addMe(about);
		}
		m_vCorner1.printMe("mvCorner1 moved");
		m_vCorner2.printMe("mvCorner2 moved");
	}
	
	public void scaleAbout(FVector scaleValue) {
		scaleValue.printMe();
		m_vCorner1.e[0] *= scaleValue.e[0];
		m_vCorner1.e[1] *= scaleValue.e[1];
		m_vCorner1.e[2] *= scaleValue.e[2];
		m_vCorner2.e[0] *= scaleValue.e[0];
		m_vCorner2.e[1] *= scaleValue.e[1];
		m_vCorner2.e[2] *= scaleValue.e[2];
	}
	
	public void rotateToNewAxisAlignment(int newAxis)
	{
		float axisValue = m_vCorner1.e[m_nAlignedToAxis];
		m_vCorner1.e[m_nAlignedToAxis] = m_vCorner1.e[newAxis];
		m_vCorner1.e[newAxis] = axisValue;
		axisValue = m_vCorner2.e[m_nAlignedToAxis];
		m_vCorner2.e[m_nAlignedToAxis] = m_vCorner2.e[newAxis];
		m_vCorner2.e[newAxis] = axisValue;
		m_nAlignedToAxis = newAxis;
	}
	
	public boolean testRay(FVector rayStart, FVector rayDir)
	{
		if (rayDir.e[m_nAlignedToAxis] == 0)
			return false;
		float multiplyer = (m_vCorner1.e[m_nAlignedToAxis] - rayStart.e[m_nAlignedToAxis]) / rayDir.e[m_nAlignedToAxis];
		if (multiplyer < 0.0f)
			return false;
		FVector intersection = rayStart.add(rayDir.multiply(multiplyer));
		for (int i = 0; i < 3; i++)
		{
			if (i != m_nAlignedToAxis)
			{
				if (! ((intersection.e[i] < m_vCorner1.e[i] && intersection.e[i] > m_vCorner2.e[i])
						|| (intersection.e[i] < m_vCorner2.e[i] && intersection.e[i] > m_vCorner1.e[i])))
					return false;
			}
		}
		return true;
	}
	
	public void debugDraw(CampusMap applet) {
		applet.fill(255, 100, 100, 255);
		finalDebugDraw(applet);
	}
	
	public void debugDraw(CampusMap applet, int color) {
		applet.fill(color);
		finalDebugDraw(applet);
	}
	
	private void finalDebugDraw(CampusMap applet) {
		applet.pushMatrix();
		applet.beginShape(PConstants.QUADS);
		if (m_nAlignedToAxis == 0)
		{
			applet.vertex(m_vCorner1.getX(), m_vCorner1.getY(), m_vCorner1.getZ());
			applet.vertex(m_vCorner1.getX(), m_vCorner2.getY(), m_vCorner1.getZ());
			applet.vertex(m_vCorner1.getX(), m_vCorner2.getY(), m_vCorner2.getZ());
			applet.vertex(m_vCorner1.getX(), m_vCorner1.getY(), m_vCorner2.getZ());
		}
		else if (m_nAlignedToAxis == 1)
		{
			applet.vertex(m_vCorner1.getX(), m_vCorner1.getY(), m_vCorner1.getZ());
			applet.vertex(m_vCorner2.getX(), m_vCorner1.getY(), m_vCorner1.getZ());
			applet.vertex(m_vCorner2.getX(), m_vCorner1.getY(), m_vCorner2.getZ());
			applet.vertex(m_vCorner1.getX(), m_vCorner1.getY(), m_vCorner2.getZ());
		}
		else
		{
			applet.vertex(m_vCorner1.getX(), m_vCorner1.getY(), m_vCorner1.getZ());
			applet.vertex(m_vCorner2.getX(), m_vCorner1.getY(), m_vCorner1.getZ());
			applet.vertex(m_vCorner2.getX(), m_vCorner2.getY(), m_vCorner1.getZ());
			applet.vertex(m_vCorner1.getX(), m_vCorner2.getY(), m_vCorner1.getZ());
		}
		applet.endShape();
		applet.popMatrix();
	}
}